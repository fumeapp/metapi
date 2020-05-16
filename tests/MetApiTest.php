<?php

namespace acidjazz\metapi\Tests;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class MetApiTest extends BaseTest
{
    private Request $request;
    private MetApiTrait $ma;

    public function __construct()
    {
        $this->request = new Request();
        $this->ma = new MetApiTrait($this->request);
        parent::__construct();
    }
    public function testConstructor()
    {
        $ma = new MetApiTrait($this->request);
        $this->assertIsFloat($ma->benchmark);
        $this->assertEquals($this->request, $ma->request);
    }

    public function testOptions()
    {
        $this->ma->option('name', 'required|string');
        $this->assertEquals(['name' => 'required|string'], $this->ma->query['options']);
        $this->ma->options(['name' => 'required|string']);
        $this->assertEquals(['name' => 'required|string'], $this->ma->query['options']);
    }

    public function testMeta()
    {
        $this->ma->addMeta('this', 'that');
        $this->assertEquals(['this' => 'that'], $this->ma->meta);
    }

}
