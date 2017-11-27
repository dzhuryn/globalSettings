[+head+]
</head>
<body>
<script type="text/javascript" src="..//assets/tvs/multitv/js/jquery-json-2.4.min.js"></script>
<script type="text/javascript" src="..//assets/tvs/multitv/js/jquery-ui-1.10.4.custom.min.js"></script>
<script type="text/javascript" src="..//assets/tvs/multitv/js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript" src="..//assets/tvs/multitv/js/jquery-field-0.9.7.min.js"></script>
<script type="text/javascript" src="..//assets/tvs/multitv/js/jquery-colorbox-1.4.33.min.js"></script>
<script type="text/javascript" src="..//assets/tvs/multitv/js/jquery-htmlClean-1.3.0.min.js"></script>
<script type="text/javascript" src="..//assets/tvs/multitv/js/multitvhelper.js"></script>
<script type="text/javascript" src="..//assets/tvs/multitv/js/multitv.js"></script>

[+forms+]
<script>
    var $ = jQuery;


    function saveValues() {
        var $ = jQuery;
      //  var data = jQuery('body form').serialize();
        jQuery('form').each(function (ind,elem) {
           var str = JSON.stringify(jQuery('form').serializeArray())
            str = str.replace("}}", "} }");
            webix.ajax().post("[+moduleurl+]action=saveValues", {data:str}, function(text){
                webix.message('Сохранено');
            })
        })

    }

    function updateTab(category) {
        $.get('[+moduleurl+]action=getForm&group='+category,function (data) {

            $$('formView_' + category).setHTML(data)

        });
        $.get('[+moduleurl+]action=getForm&group=edit',function (data) {
            $$('formView_edit').setHTML(data)
        });
        webix.message('Обновлено')
    }
    function save() {
        var field = $$("fieldInfo").getValues();

        webix.ajax().get("[+moduleurl+]action=saveField", { field: field }, function(text){
            text = JSON.parse(text);

            if(text['status'] === 0){
                webix.message( text['text'] )
            }
            else if(text['status'] === 1){
                webix.message( text['text'] );
                popup.hide();
                setTimeout(function () {
                    location.reload()
                },300);
            }
            else if(text['status'] === 2){
                webix.message( text['text'] );
                var category = text['category'];
                popup.hide();
                updateTab(category);
                $$("fieldInfo").clear();

            }


        });
    }

    var lastImageCtrl;
    var lastFileCtrl;
    function OpenServerBrowser(url, width, height ) {
        var iLeft = (screen.width  - width) / 2 ;
        var iTop  = (screen.height - height) / 2 ;

        var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
        sOptions += ',width=' + width ;
        sOptions += ',height=' + height ;
        sOptions += ',left=' + iLeft ;
        sOptions += ',top=' + iTop ;

        var oWindow = window.open( url, 'FCKBrowseWindow', sOptions ) ;
    }
    function BrowseServer(ctrl) {

        lastImageCtrl = ctrl;
        var w = screen.width * 0.5;
        var h = screen.height * 0.5;
        OpenServerBrowser('//[+site_url+]/manager/media/browser/mcpuk/browser.php?Type=images', w, h);
    }
    function BrowseFileServer(ctrl) {
        lastFileCtrl = ctrl;
        var w = screen.width * 0.5;
        var h = screen.height * 0.5;
        OpenServerBrowser('//[+site_url+]/manager/media/browser/mcpuk/browser.php?Type=files', w, h);
    }
    function SetUrlChange(el) {
        if ('createEvent' in document) {
            var evt = document.createEvent('HTMLEvents');
            evt.initEvent('change', false, true);
            el.dispatchEvent(evt);
        } else {
            el.fireEvent('onchange');
        }
    }
    function SetUrl(url, width, height, alt) {

        if(lastFileCtrl) {
            var c = document.getElementById(lastFileCtrl);
            if(c && c.value != url) {
                c.value = url;
                SetUrlChange(c);
            }
            lastFileCtrl = '';
        } else if(lastImageCtrl) {
            var c = document.getElementById(lastImageCtrl);
            var cc = document.getElementById(lastImageCtrl+"_image");
            console.log(cc);
            if(c && c.value != url) {
                c.value = url;
                if(cc) {
                    cc.src = "/"+url;
                }
                SetUrlChange(c);
            }
            lastImageCtrl = '';
        } else {
            return;
        }
    }

    function afterTabLoad (data) {
        alert(data)
    }

    var popup = webix.ui({
        view: "popup",
        id: "my_popup",
        height: 400,
        width: 800,
        position: "center",
        move:true,
        body: {

            rows: [
                {
                    view: "form",
                    id:"fieldInfo",
                    elements: [
                        { name:"id", labelWidth: 200, body:"123", label: "Id"},
                        { name:"name", labelWidth: 200, view: "text", label: "Имя поля"},
                        { name:"description", labelWidth: 200, view: "text", label: "Описание поля"},
                        { name:"type", labelWidth: 200, view: "select", label: "Тип поля", options: [
                            {value: "Text", id: "text"},
                            {value: "Textarea", id: "textarea"},
                            {value: "Richtext", id: "richtext"},
                            {value: "Checkbox", id: "checkbox"},
                            {value: "Select", id: "select"},
                            {value: "Image", id: "image"},
                            {value: "File", id: "file"},
                            {value: "Multitv", id: "multitv"}
                        ]
                        },
                        { name:"elements", labelWidth: 200, view: "textarea", label: "Возможные значения"},
                        { name:"category", labelWidth: 200, view: "select", label: "Категоия",options:"[+moduleurl+]action=get-category",},
                        { name:"newCategory",labelWidth: 200, view: "text", label: "Новая категория"},
                    ]
                },
                {
                    view: "toolbar", cols: [
                    {view: "button", value: "Сохранить", width: 100, click: save },
                    {
                        view: "button", value: "Отменить", width: 100, click: function () {
                        popup.hide()
                    }
                    },

                ]
                }
            ]
        }
    });


$('body').on('click','.webix_item_tab',function () {
$(document).ready();
    $(document).trigger('ready')
})
function test() {
    $('.tab-content').each(function (index,tab) {
        element = $(tab).find('script');
        element.each(function (ind,elem) {
            el = $(elem).html();
            eval(el)
        });
    });

    tinymce.init({ selector:'textarea.richtext',
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });
    $(".field-group").sortable();


}

setTimeout(function () {
    webix.ui({
        rows: [

            {
                cols: [
                    {
                        view: "template",
                        type: "header", template: "Настройки "
                    },
                    { view:"icon", icon:"plus-square", align:"left", click:function () {
                        $$("fieldInfo").clear()
                        popup.show()
                    }},
                    { view:"icon", icon:"save", click:saveValues, align:"left"},

                ]


            },
            {view: "resizer"},
            {
                view: "tabview",
                scroll:true,
                cells:[+tabs+],

            },
            {
                cols: [
                    {
                        view: "button",
                        id: "reload",
                        value: "Обновить страницу",
                        type: "form",
                        inputWidth: 200,
                        click: function () {
                            $$('formView_' + 1).setHTML('123')
                        }
                    },


                ]
            }


        ],

    })
},1);

    function load_form() {
        ;
    }
   // popup.show();
</script>
[+footer+]

</body>
</html>