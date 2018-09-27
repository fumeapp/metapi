<?php

namespace acidjazz\metapi;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Validator;
use JasonGrimes\Paginator;

abstract class MetApiController extends BaseController
{

  protected $request;
  protected $benchmark;

  protected $query = [
    'defaults' => [],
    'options' => [],
    'params' => [],
    'combined' => [],
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
    if ($default !== false) {
      $this->query['defaults'][$name] = $default;
    }
  }

  protected function options($options) {
    foreach ($options as $key=>$value) {
      $this->option($key, $value);
    }
  }

  protected function addMeta($name, $value) {
    $this->meta[$name] = $value;
  }

  protected function paginate($collection,$perpage=15) {

    $collection = $collection->paginate($perpage);

    $paginator = new Paginator(
      $collection->total(),
      $collection->perPage(),
      $collection->currentPage()
    );

    $paginator->setMaxPagesToShow(7);

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

    $this->query['combined'] = $this->query['defaults'];

    foreach ($this->query['params'] as $key=>$value) {
      $this->query['combined'][$key] = $value;
    }

    return $this->query;

  }

  protected function getMeta() {
    $this->meta['benchmark'] = microtime(true)-$this->benchmark;
    return $this->meta;
  }

  protected function getParam($key) {
    $this->verify();

    if (!isset($this->query['params'][$key])) {
      return false;
    }

    return $this->query['params'][$key];

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

    $this->render(['errors' => $this->errors], 500);
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
  protected function success($message='Successful',$replace=[])
  {
    return $this->render(['success' => true,'type' => 'success', 'message' => __($message,$replace)], 200, true);
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
      $response['query'] = $this->verify();
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
      $responsable = response(view('metapi::json', ['json' => json_encode($response, JSON_PRETTY_PRINT)]), $code);
    } else {
      $responsable = response()->json($response, $code, [], JSON_PRETTY_PRINT);
    }

    if ($abort) {
      return abort($responsable);
    }

    return $responsable;

  }

}
