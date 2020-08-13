<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/svg/20170401/b49f30849c.png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsoneditor@9.0.3/dist/jsoneditor.css"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsoneditor@9.0.3/dist/jsoneditor.js"></script>
    <style lang="text/css">
        body, html {
          margin: 0;
          padding: 0;
          font-family: 'Roboto Mono', monospace !important;
          font-size: 16px !important;
          background: #282a36;
        }

        #editor {
            height: 100vh;
        }

        #json, .jsoneditor-expand-all, .jsoneditor-collapse-all {
            display: none;
        }

        table.jsoneditor-tree > tbody > tr.jsoneditor-expandable:first-child {
            display: none;
        }

        table.jsoneditor-tree {
            margin-left: -27px;
        }

        div.jsoneditor-field,
        div.jsoneditor-value,
        div.jsoneditor td,
        div.jsoneditor th,
        div.jsoneditor textarea,
        div.jsoneditor pre.jsoneditor-preview,
        div.jsoneditor .jsoneditor-schema-error {
            font-family: 'Roboto Mono', monospace !important;
            font-size: 16px !important;
        }

        .title {
            display: block;
            font-weight: bold;
            padding: 5px;
            padding-left: 10px;
            animation: slide-in-right 0.2s ease 0s;
            color: white;
            margin-top: 7px;
        }
        .version {
            font-weight: normal;
            font-size: 12px;
            margin-left: 10px;
            background-color: #fb5661;
            padding: 2px 8px 3px 8px;
            border-radius: 10px;
        }

        div.jsoneditor {
            border: 0 solid #888;
        }

        div.jsoneditor-menu {
            border-bottom: 0 solid #888;
            background-color: #2d3748;
            color: #718096;
            height: 46px;
        }

        div.jsoneditor-search  {
            margin: 6px;
        }
        div.jsoneditor-search div.jsoneditor-frame {
            border-radius: 10px;
            background-color: #282a36;
            color: #2b2728;
        }
        div.jsoneditor .jsoneditor-search input {
            background-color: #282a36;
            color: white;
        }

        div.jsoneditor-field {
            color: white;
        }

        div.jsoneditor-value {
            color: #718096;
        }

        div.jsoneditor-readonly {
            color: #6272a4;
        }

        div.jsoneditor-value.jsoneditor-string {
            color: #f1fa8c;
        }
        a.jsoneditor-value.jsoneditor-url {
            color: #8be9fd;
        }

        div.jsoneditor-value.jsoneditor-number {
            color: #bd93f9;
        }

        div.jsoneditor-field.jsoneditor-highlight,
        div.jsoneditor-field.jsoneditor-highlight-active,
        div.jsoneditor-field.jsoneditor-highlight-active:focus,
        div.jsoneditor-field.jsoneditor-highlight-active:hover,
        div.jsoneditor-value.jsoneditor-highlight-active,
        div.jsoneditor-value.jsoneditor-highlight,
        div.jsoneditor-value.jsoneditor-highlight-active:focus,
        div.jsoneditor-value.jsoneditor-highlight-active:hover {
            color: #272727;
        }

        div.jsoneditor-value.jsoneditor-null {
            color: #50fa7b;
        }

        @keyframes slide-in-right {
          from {
            opacity: 0;
            transform: translate(-20px, 0);
          to {
            opacity: 1;
            transform: translate(0, 0);
          }
        }
    </style>
  </head>
  <body>
    <div id="editor"> </div>
    <div id="json"> {{ $json }} </div>
    <script>
        let container = document.getElementById('editor')
        let options = {
            name: 'response',
            mode: 'view',
            navigationBar: false,
        }
        let content = JSON.parse(document.getElementById('json').innerHTML)
        let editor = new JSONEditor(container, options)
        editor.set(content)
        editor.expandAll()
        let title = document.createElement('div')
        title.appendChild(document.createTextNode('metapi'))
        title.className = 'title'
        let version = document.createElement('span')
        version.className = 'version'
        version.appendChild(document.createTextNode('v2.1.0'))
        title.appendChild(version)
        document.getElementsByClassName('jsoneditor-menu')[0].insertBefore(title, document.getElementsByClassName('jsoneditor-serach')[0])
    </script>
  </body>
</html>
