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

    protected $query = [
        'options' => [],
        'params' => [],
    ];

    protected $errors = [];

    protected $meta = [];
    protected $compiled = false;

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

    protected function paginate($collection,$perpage=15,$maxPages=7) {

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
            'next_page_url' => $collection->nextPageUrl(),
            'prev_page_url' => $collection->previousPageUrl(),
            'first_item' => $paginator->getCurrentPageFirstItem(),
            'last_item' => $paginator->getCurrentPageLastItem(),
            'pages' => $pages,
        ]);

        return $collection->items();

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

    protected function addError($type,$message,$file=null,$line=null)
    {
        $error = [
            'type' => $type,
            'message' => $message,
        ];

        if ($file !== null) {
            $error['file'] = $file;
        }

        if ($line !== null) {
            $error['line'] = $line;
        }

        $this->errors[$type][] = $message;

        return $this;
    }

    /**
     * render errors
     * returns $this->errors w/ no view, transformer and an error code of 500
     */

    protected function error($key='unknown',$replace=[]) {

        if ($key !== 'unknown' || count($this->errors) < 1) {
            $this->addError($key, __($key,$replace));
        }

        return $this->render(['errors' => $this->errors], 500);
    }

    /**
     * render errors and abort
     */
    protected function abort() {
        $this->render(['errors' => $this->errors], 500, true);
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

        if ($code === 403 || count($this->errors) > 0) {
            $response = $data;
            $code = 403;
        } else {
            $response = $this->getMeta();
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
