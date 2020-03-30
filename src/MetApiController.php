<?php

namespace acidjazz\metapi;

use Illuminate\{Routing\Controller as BaseController,
    Foundation\Bus\DispatchesJobs,
    Foundation\Validation\ValidatesRequests,
    Foundation\Auth\Access\AuthorizesRequests,
    Http\Request
};

use Validator;
use JasonGrimes\Paginator;

abstract class MetApiController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $request;
    protected $benchmark;
    protected $status;

    protected $query = [
        'options' => [],
        'params' => [],
    ];

    protected $errors = [];

    protected $meta = [];
    protected $compiled = false;

    // Whether or not we want to return the paginate items or the entire structure
    protected $paginateItems = false;

    public function setPaginateItems($boolean)
    {
        $this->paginateItems = $boolean;
    }

    public function __construct(Request $request)
    {
        $this->benchmark = microtime(true);
        $this->request = $request;
    }

    /**
     * push an option to our query stack
     *
     * @param string $name
     * @param string $type
     * @param boolean $default
     * @return MetApiController
     */
    protected function option($name, $type, $default = false)
    {
        $this->query['options'][$name] = $type;
        return $this;
    }

    /**
     * push multiple options to our query stack
     *
     * @param array $options
     * @return MetApiController
     */
    protected function options($options)
    {
        foreach ($options as $key => $value) {
            $this->option($key, $value);
        }
        return $this;
    }

    /**
     * add metadata
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    protected function addMeta($name, $value)
    {
        $this->meta[$name] = $value;
    }

    /**
     * add pagination metadata to the meta stack
     *
     * @param mixed $collection
     * @param integer $perpage
     * @param integer $maxPages
     * @return mixed
     */
    protected function paginate($collection, $perpage = 50, $maxPages = 10)
    {
        if (is_string($collection)) {
            $collection = $collection::paginate($perpage);
        } else {
            $collection = $collection->paginate($perpage);
        }

        $paginator = new Paginator(
            $collection->total(),
            $collection->perPage(),
            $collection->currentPage()
        );

        $paginator->setMaxPagesToShow($maxPages);

        $pages = [];

        foreach ($paginator->getPages() as $page) {
            $pages[] = $page['num'];
        }

        $this->addMeta('paginate', [
            'total' => $collection->total(),
            'per_page' => $collection->perPage(),
            'current_page' => $collection->currentPage(),
            'last_page' => $collection->lastPage(),
            'first_item' => $paginator->getCurrentPageFirstItem(),
            'last_item' => $paginator->getCurrentPageLastItem(),
            'pages' => $pages,
        ]);

        if ($this->paginateItems) {
            return $collection->items();
        }

        return $collection;
    }

    /**
     * verify through laravels Validator
     *
     * @param boolean $abort
     * @return array|bool|void
     */
    protected function verify($abort = true)
    {

        $validate = Validator::make($this->request->all(), $this->query['options']);

        if ($validate->fails()) {

            foreach ($validate->errors()->toArray() as $key => $value) {
                foreach ($value as $error) {
                    $this->addError($key, $error);
                }
            }

            if ($abort) {
                return $this->abort();
            } else {
                return false;
            }

        }

        foreach ($this->request->all() as $key => $value) {
            if (isset($this->query['options'][$key])) {

                if ($this->isFile($value)) {
                    $value = (array)$value;
                }

                if (is_array($value)) {
                    foreach ($value as $bkey => $bvalue) {
                        if (is_resource($bvalue)) {
                            unset($value[$bkey]);
                        }
                        if ($this->isFile($bvalue)) {
                            $value[$bkey] = (array)$bvalue;
                        }
                    }
                }

                $this->query['params'][$key] = $value;
            }
        }

        return $this->query;
    }

    /**
     * Detect if a value is an object and of type File
     *
     * @param mixed $value
     * @return boolean
     */
    private function isFile($value)
    {
        return is_object($value) && in_array(get_class($value),
                ['Illuminate\Http\UploadedFile', 'Illuminate\Http\Testing\File']);
    }

    /**
     * return our metadata stack and benchmark the request
     *
     * @return array
     */
    protected function getMeta()
    {
        $this->meta['benchmark'] = microtime(true) - $this->benchmark;
        return $this->meta;
    }

    /**
     * add an error to our error stack
     *
     * @param string $title
     * @param string $detail
     * @param integer $status
     * @param boolean $source
     * @return MetApiController
     */
    protected function addError($title, $detail, $status = 400, $source = false)
    {
        $error = ['status' => $status, 'title' => $title,];

        if ($source) {
            $error['source'] = $source;
        }

        $error['detail'] = $detail;

        $this->errors[] = $error;

        return $this;
    }

    /**
     * Render errors
     *
     * @param string $key - shortkey or title of error
     * @param string|array $replace - shortkey params
     * @param integer $status - HTTP status code to pass
     * @param bool $source
     * @return \Illuminate\Http\Response
     */
    protected function error($key, $replace = null, $status = 400, $source = false)
    {
        if (__($key, is_array($replace) ? $replace : []) !== $key) {
            $this->addError($key, __($key, is_array($replace) ? $replace : []), $status, $source);
        } else {
            $this->addError($key, $replace, $status, $source);
        }
        return $this->render(['errors' => $this->errors], $status);
    }

    /**
     * render errors and abort
     */
    protected function abort()
    {
        $this->render(['errors' => $this->errors], 400, true);
    }

    /**
     * Render success
     * @param string
     * @param array
     * @return \Illuminate\Http\Response
     */
    protected function success($message = 'Successful', $replace = [], $data = [])
    {
        return $this->render([
            'success' => true,
            'type' => 'success',
            'message' => __($message, $replace),
            'data' => $data,
        ], 200, true);
    }

    /**
     * Final output
     * @param mixed $data data to be sent
     * @param integer $code response code, defaulting to 200
     * @param bool $abort
     * @return mixed
     */
    protected function render($data = false, $code = 200, $abort = false)
    {

        if (in_array($code, [400, 403, 500]) || count($this->errors) > 0) {
            $response['status'] = 'error';
            $response = array_merge($response, $data);
        } else {
            $response['status'] = 'success';
            $response = array_merge($response, $this->getMeta());
            $response['query'] = $this->query;
            $response['data'] = $data;
        }

        if ($this->request->query('callback') !== null) {
            $json = json_encode($response, JSON_PRETTY_PRINT);
            $response = ['callback' => $this->request->query('callback'), 'json' => $json];
            $responsable = response(view('metapi::jsonp', $response), 200)->header('Content-type', 'text/javascript');
        } else {
            if (
                strpos($this->request->header('accept'), 'text/html') !== false &&
                config('app.debug') === true && $this->request->query('json') !== 'true') {
                $responsable = response(view('metapi::json', ['json' => json_encode($response, true)]), $code);
            } else {
                $responsable = response()->json($response, $code, [], JSON_PRETTY_PRINT);
            }
        }

        if ($abort) {
            return abort($responsable);
        }

        return $responsable;
    }
}
