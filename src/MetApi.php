<?php

namespace acidjazz\metapi;

use Illuminate\Http\Request;
use Validator;
use JasonGrimes\Paginator;

trait MetApi
{
    public Request $request;
    public float $benchmark;
    public string $status;

    /** @var array|array[] $query */
    public array $query = [
        'options' => [],
        'params' => [],
    ];

    /** @var array $errors */
    public array $errors = [];

    /** @var array $meta */
    public array $meta = [];

    /**
     * MetApi constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->metApiInit($request);
    }

    /**
     * @param Request $request
     */
    public function metApiInit(Request $request)
    {
        $this->benchmark = microtime(true);
        $this->request = $request;
    }

    /**
     * Push option to validation stack
     * @see https://laravel.com/docs/9.x/validation#available-validation-rules
     *
     * @param string $name
     * @param array|string $rules
     * @param array $messages<string, string>
     * @return Controller
     */
    public function option(string $name, array|string $rules, array $messages = []): self
    {
        $this->query['options']['rules'][$name] = $rules;

        if (! empty($messages)) {
            $colMessages = array_map(
                fn ($message, $key) => [$name . '.' . $key => $message],
                $messages,
                array_keys($messages)
            );

            $this->query['options']['messages'] = array_merge(
                $this->query['options']['messages'] ?? [],
                ...$colMessages
            );
        }

        return $this;
    }

    /**
     * push multiple options to our query stack
     *
     * @param array $options
     * @return MetApi
     */
    public function options($options)
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
     * @param mixed $value
     *
     * @return void
     */
    public function addMeta($name, $value)
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
    public function paginate($collection, $perpage = 50, $maxPages = 10)
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

        return $collection->items();
    }

    /**
     * Verify through the Validator
     *
     * @param boolean $abort
     * @return array|bool|void
     */
    public function verify($abort = true)
    {

        $validate = Validator::make($this->request->all(), $this->query['options']['rules'], $this->query['options']['messages'] ?? []);

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
            if (isset($this->query['options']['rules'][$key])) {
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
        return is_object($value) && in_array(
            get_class($value),
            ['Illuminate\Http\UploadedFile', 'Illuminate\Http\Testing\File']
        );
    }

    /**
     * return our metadata stack and benchmark the request
     *
     * @return array
     */
    public function getMeta()
    {
        $this->meta['benchmark'] = microtime(true) - $this->benchmark;
        return $this->meta;
    }

    /**
     * add an error to our error stack
     *
     * @param string $message
     * @param string $detail
     * @param integer $status
     * @param boolean $source
     * @return MetApi
     */
    public function addError($message, $detail, $status = 400, $source = false)
    {
        $error = ['status' => $status, 'message' => $message,];

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
     * @param string $key - shortkey or error message
     * @param string|array $replace - shortkey params
     * @param integer $status - HTTP status code to pass
     * @param bool $source
     * @return mixed
     */
    public function error($key, $replace = null, $status = 400, $source = false)
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
    public function abort()
    {
        $this->render(['errors' => $this->errors], 400, true);
    }

    /**
     * Render success
     * @param string
     * @param array $replace
     * @param array $data
     * @return mixed
     */
    public function success($message = 'Successful', $replace = [], $data = [])
    {
        return $this->render([
            'success' => true,
            'type' => 'success',
            'message' => __($message, $replace),
            'data' => $data,
        ], 200, true);
    }

    /**
     * Render success
     * @param string
     * @param array $replace
     * @param array $data
     * @return mixed
     */
    public function warn($message = 'Warning', $replace = [], $data = [])
    {
        return $this->render([
            'success' => false,
            'type' => 'warning',
            'message' => __($message, $replace),
            'data' => $data,
        ], 200, true);
    }


    /**
     * Final output
     * @param mixed $data data to be sent
     * @param int $code response code, defaulting to 200
     * @param bool $abort
     * @return mixed
     */
    public function render($data = false, $code = 200, $abort = false)
    {

        if (in_array($code, [400, 403, 500]) || count($this->errors) > 0) {
            $response['status'] = 'error';
            $response = array_merge($response, $data);
        } else {
            $response['status'] = 'success';
            $response = array_merge($response, $this->getMeta());
            $response['query'] = $this->normalizeQuery($this->query);
            $response['data'] = $data;
        }

        if ($this->request->query('callback') !== null) {
            $json = json_encode($response, JSON_PRETTY_PRINT);
            $response = ['callback' => $this->request->query('callback'), 'json' => $json];
            $responsable = response(view('metapi::jsonp', $response), 200)->header('Content-type', 'text/javascript');
        } elseif (strpos($this->request->header('accept'), 'text/html') !== false &&
            config('app.debug') === true && $this->request->query('json') !== 'true') {
            $responsable = response(view('metapi::json', ['json' => json_encode($response, true)]), $code);
        } else {
            $responsable = response()->json($response, $code, [], JSON_PRETTY_PRINT);
        }

        if ($abort) {
            return abort($responsable);
        }

        return $responsable;
    }

    /**
     * Normalize query metadata
     *
     * @param array $query
     * @return array
     */
    private function normalizeQuery(array $query): array
    {
        $output = [];
        $params = $query['params'] ?? [];
        $options = [];
        $rules = $query['options']['rules'] ?? [];

        foreach ($rules as $key => $value) {
            $options[$key] = is_array($value) ? $value : explode('|', $value);
        }

        $output['options'] = $options;
        $output['params'] = $params;

        return $output;
    }
}
