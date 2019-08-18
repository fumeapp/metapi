<!doctype html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="https://png.pngtree.com/svg/20170401/b49f30849c.png" />
    <link rel="stylesheet", href="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/6.3.0/jsoneditor.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsoneditor/6.3.0/jsoneditor.min.js" integrity="sha256-o2/L37SBn4ufKxtZAItcvBdidVsP170IKNcqfVd3ZF4=" crossorigin="anonymous"></script>
    <style lang="text/css">
        body, html {
          margin: 0;
          padding: 0;
          font-family: 'Roboto Mono', monospace !important;
          font-size: 16px !important;
          background-color: #272727;
          color: #ebdab4;
        }


        div.phpdebugbar {
          font-family: 'Roboto Mono', monospace !important;
          font-size: 13px !important;
          background: #272727 !important;
          color: #ebdab4 !important;
        }

        div.phpdebugbar-header,
        a.phpdebugbar-restore-btn,
        div.phpdebugbar-openhandler .phpdebugbar-openhandler-header {
          background-color: #3c3836 !important;
        }

        div.phpdebugbar-header > div > * {
          color: #ebdab4 !important;
        }

        div.phpdebugbar-mini-design a.phpdebugbar-tab {
          border-right: 1px solid #888 !important;
        }


        div.phpdebugbar .hljs-comment,
        div.phpdebugbar .hljs-template_comment,
        div.phpdebugbar .diff .hljs-header,
        div.phpdebugbar .hljs-javadoc {
          color: #998;
          font-style: italic
        }

        div.phpdebugbar .hljs-keyword,
        div.phpdebugbar .css .rule .hljs-keyword,
        div.phpdebugbar .hljs-winutils,
        div.phpdebugbar .javascript .hljs-title,
        div.phpdebugbar .nginx .hljs-title,
        div.phpdebugbar .hljs-subst,
        div.phpdebugbar .hljs-request,
        div.phpdebugbar .hljs-status {
          color: #ebdab4 !important;
        }

        div.phpdebugbar .hljs-number,
        div.phpdebugbar .hljs-hexcolor,
        div.phpdebugbar .ruby .hljs-constant {
          color: #f54a3a !important;
        }

        div.phpdebugbar .hljs-string,
        div.phpdebugbar .hljs-tag .hljs-value,
        div.phpdebugbar .hljs-phpdoc,
        div.phpdebugbar .tex .hljs-formula {
          color: #b2b535 !important;
        }

        div.phpdebugbar .hljs-title,
        div.phpdebugbar .hljs-id,
        div.phpdebugbar .coffeescript .hljs-params,
        div.phpdebugbar .scss .hljs-preprocessor {
          color: #900;
          font-weight: bold
        }

        a.phpdebugbar-tab span.phpdebugbar-badge {
          background: #f54a3a !important;
          color: #ebdab4 !important;
        }

        a.phpdebugbar-tab.phpdebugbar-active {
          background: #f54a3a !important;
          color: #ebdab4 !important;
        }

        a.phpdebugbar-tab.phpdebugbar-active span.phpdebugbar-badge {
          background-color: #ebdab4 !important;
          color: #f54a3a !important;
        }

        ul.phpdebugbar-widgets-list li.phpdebugbar-widgets-list-item:nth-child(even) {
          background-color: #3c3836 !important;
        }

        div.phpdebugbar code, div.phpdebugbar pre {
          color: #f6bc41 !important;
        }

        ul.phpdebugbar-widgets-list li.phpdebugbar-widgets-list-item .phpdebugbar-widgets-params {
          background-color: #3c3836 !important;
        }

        .phpdebugbar-text-muted {
          color: #8cbd7e !important;
        }

        div.phpdebugbar-widgets-templates div.phpdebugbar-widgets-status {
          background-color: #a79985 !important;
          color: #ebdab4;
        }

        ul.phpdebugbar-widgets-list li.phpdebugbar-widgets-list-item:hover {
          background: #504945 !important;
        }

        a.phpdebugbar-tab:hover,
        span.phpdebugbar-indicator:hover,
        a.phpdebugbar-indicator:hover,
        a.phpdebugbar-close-btn:hover,
        a.phpdebugbar-open-btn:hover {
            background-color: #ebdab4 !important;
            color: #262626 !important;
            transition: background-color .25s linear 0s, color .25s linear 0s;
        }

        ul.phpdebugbar-widgets-timeline li:nth-child(even) {
          background-color: #504945 !important;
          color: #ebdab4 !important;
        }

        .phpdebugbar-indicator span.phpdebugbar-tooltip {
          background-color: #ebdab4 !important;
          color: #272727 !important;
        }



        ul.phpdebugbar-widgets-timeline li span.phpdebugbar-widgets-label,
        ul.phpdebugbar-widgets-timeline li span.phpdebugbar-widgets-collector {
          color: #ebdab4 !important;
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

        #json {
          display: none;
        }

        .title {
          display: block;
          font-weight: bold;
          padding: 5px;
          padding-left: 70px;
          animation: slide-in-right 0.2s ease 0s;
          color: #83a191;
        }

        div.jsoneditor {
          border: 0px solid #888;
        }

        div.jsoneditor-menu {
          background-color: #3c3836;
          border-bottom: 0px solid #888;
        }

        div.jsoneditor-navigation-bar {
          border-bottom: 0px solid #888;
        }

        table.jsoneditor-search div.jsoneditor-frame {
          background-color: #a79985;
          color: #2b2728;
        }

        div.jsoneditor .jsoneditor-search input {
          color: white;
        }


        table.jsoneditor-search button.jsoneditor-refresh {
          background-position: -99px -48px;
        }

        table.jsoneditor-search button.jsoneditor-next {
          background-position: -124px -48px;
        }
        table.jsoneditor-search button.jsoneditor-previous {
          background-position: -148px -48px;
        }


        div.jsoneditor-treepath {
          background-color: #a79985;
          color: #272727;
        }

        div.jsoneditor-field {
          color: #ebdab4;
        }
        div.jsoneditor-value {
          color: #f9bc40;
        }

        div.jsoneditor-value.jsoneditor-string {
          color: #b2b535;
        }

        div.jsoneditor-value.jsoneditor-null {
          color: #8cbd7e;
        }

        div.jsoneditor-value.jsoneditor-num {
          color: #f54a3a;
        }

        div.jsoneditor-tree button.jsoneditor-button:focus {
          background-color: #a79985;
          outline: 1px solid #888;
        }

        div.jsoneditor-field.jsoneditor-highlight-active,
        div.jsoneditor-field.jsoneditor-highlight-active:focus,
        div.jsoneditor-field.jsoneditor-highlight-active:hover,
        div.jsoneditor-value.jsoneditor-highlight-active,
        div.jsoneditor-value.jsoneditor-highlight-active:focus,
        div.jsoneditor-value.jsoneditor-highlight-active:hover {
          color: #272727;
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
        let content = JSON.parse(document.getElementById('json').innerHTML)
        let editor = new JSONEditor(container, {mode: 'view', navigationBar: true})
        editor.set(content)
        editor.expandAll()
        let title = document.createElement('div')
        title.appendChild(document.createTextNode('metapi v1.4.0'))
        title.className = 'title'
        let menu = document.getElementsByClassName('jsoneditor-menu')[0].insertBefore(title, document.getElementsByClassName('jsoneditor-serach')[0])
    </script>
  </body>
</html>
