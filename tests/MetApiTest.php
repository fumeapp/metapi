<?php

namespace acidjazz\metapi\Tests;

use Illuminate\Http\Request;

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

    public function testVerifyError()
    {
        $this->ma->option('name', 'required|string');
        $this->ma->verify(false);
        $this->assertEquals(
            [[
            'status' => 400,
            'title' => 'name',
            'detail' => 'The name field is required.',
            ]],
            $this->ma->errors
        );
    }
    public function testVerifySuccess()
    {
        $this->request->request->add(['name' => 'bob']);
        $this->ma->verify(false);
        $this->assertEquals([], $this->ma->errors);
    }

    public function testAddError()
    {
        $this->ma->error('title', 'detail', 420, true);
        $this->assertEquals(
            [[
            "status" => 420,
            "title" => "title",
            "source" => true,
            "detail" => "detail",
            ]],
            $this->ma->errors
        );
    }

    public function testAbort()
    {
        try {
            $this->ma->abort();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $this->assertInstanceOf(\Illuminate\Http\Exceptions\HttpResponseException::class, $e);
        }
    }

    public function testSuccess()
    {
        try {
            $this->ma->success();
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            $this->assertInstanceOf(\Illuminate\Http\Exceptions\HttpResponseException::class, $e);
        }
    }
}
