<?php

namespace acidjazz\metapi;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Http\Request;
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

    // Wether or not we want to return the paginate items or the entire structure
    protected $paginateItems = true;

    public function setPaginateItems($boolean)
    {
        $this->paginateItems = $boolean;
    }

    public function __construct(Request $request) {
        $this->benchmark = microtime(true);
        $this->request = $request;
    }

    protected function option($name, $type, $default=false) {
        $this->query['options'][$name] = $type;
        return $this;
    }

    protected function options($options) {
        foreach ($options as $key=>$value) {
            $this->option($key, $value);
        }
        return $this;
    }

    protected function addMeta($name, $value) {
        $this->meta[$name] = $value;
    }

    protected function paginate($collection,$perpage=50,$maxPages=10) {

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

    protected function verify($abort=true) {

        $validate = Validator::make($this->request->all(), $this->query['options']);

        if ($validate->fails()) {

            foreach ($validate->errors()->toArray() as $key=>$value) {
                foreach($value as $error) {
                    $this->addError($key, $error);
                }
            }

            if ($abort) {
                return $this->abort();
            } else {
                return false;
            }

        }

        foreach ($this->request->all() as $key=>$value) {
            if (isset($this->query['options'][$key])) {
                $this->query['params'][$key] = $value;
            }
        }

        return $this->query;

    }

    protected function getMeta() {
        $this->meta['benchmark'] = microtime(true)-$this->benchmark;
        return $this->meta;
    }

    protected function addError($title, $detail, $status=400, $source=false )
    {
        $error = [ 'status' => $status, 'title' => $title, ];

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
     * @param string $key       - shortkey or title of error
     * @param string|array $replace    - shortkey params
     * @param integer $status   - HTTP status code to pass
     * @param source array      - error source
     * @return \Illuminate\Http\Response
     */
    protected function error($key,$replace=null, $status=400, $source=false) {

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
    protected function abort() {
        $this->render(['errors' => $this->errors], 400, true);
    }

    /**
     * Render success
     * @param String
     * @param Array
     * @return \Illuminate\Http\Response
     */
    protected function success($message='Successful',$replace=[],$data=[])
    {
        return $this->render([
            'success' => true,
            'type' => 'success',
            'message' => __($message,$replace),
            'data' => $data,
        ], 200, true);
    }

    /**
     * Final output
     * @param mixed $data data to be sent
     * @param integer $code response code, defaulting to 200
     * @return \Illuminate\Http\Response
     */
    protected function render($data=false,$code=200,$abort=false) {

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
            $response = ['callback' => $this->request->query('callback'),'json' => $json];
            $responsable = response(view('metapi::jsonp', $response), 200)->header('Content-type', 'text/javascript');
        } else if (
            strpos($this->request->header('accept'),'text/html') !== false &&
            config('app.debug') === true && $this->request->query('json') !== 'true')
        {
            $responsable = response(view('metapi::json', ['json' => json_encode($response, true)]), $code);
        } else {
            $responsable = response()->json($response, $code, [], JSON_PRETTY_PRINT);
        }

        if ($abort) {
            return abort($responsable);
        }

        return $responsable;

    }

}
