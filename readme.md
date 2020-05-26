
<p align="center">
  <img src="https://github.com/acidjazz/metapi/raw/master/logo.png"/>
</p>

> Own your endpoint

[![Latest Stable Version](https://poser.pugx.org/acidjazz/metapi/version.png)](https://packagist.org/packages/acidjazz/metapi)
[![Total Downloads](https://poser.pugx.org/acidjazz/metapi/d/total.png)](https://packagist.org/packages/acidjazz/metapi)
[![codecov](https://codecov.io/gh/acidjazz/metapi/branch/master/graph/badge.svg)](https://codecov.io/gh/acidjazz/metapi)

<img src="https://github.com/acidjazz/metapi/blob/master/media/capture.jpg?raw=true"/>

> dark theme with laravel-debugbar


## Features
* Endpoint benchmarking
* Laravel Validation wrapper that reflects requirements
* Support for JSON and JSONP
* Interactive tree browsing with search thanks to [jsoneditor](https://github.com/josdejong/jsoneditor)


## Installation

Install metapi with [composer](https://getcomposer.org/doc/00-intro.md):
```bash
composer require acidjazz/metapi
```

Extend this library
> (`app/Http/Controllers/Controller.php` is recommended)
```php
use acidjazz\metapi\MetApi;
class Controller
{
    use Metapi;  
```

## Examples 

```php
<?php

namespace App\Http\Controllers;

use acidjazz\metapi\MetApi;

class OrgController extends Controller
{

  use MetApi;

    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        $this
          ->option('approved', 'nullable|boolean');
          ->option('type', 'nullable|in:this,that');
          ->verify();
        ...
        $this->render($results);
```

`GET /endpoint?approved=1`

```json
{
    "benchmark": 0.011060953140258789,
    "query": {
        "defaults": [],
        "options": {
            "approved": "nullable|boolean",
            "type": "nullable|in:this,that"
        },
        "params": {
            "approved": "1"
        },
        "combined": {
            "approved": "1"
        }
    },
    "data": [
        {
```

`GET /endpoint?callback=bob`

```js
bob({
    "benchmark": 0.011017084121704102,
    "query": {
        "defaults": [],
        "options": {
            "approved": "nullable|boolean",
            "type": "nullable|in:this,that"
        },
        "params": [],
        "combined": []
    },
    "data": [
        {
```

