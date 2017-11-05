// $(document).ready(function () {
//     tinymce.init({ selector:'textarea.richtext' });
// })
var ajaxUrl = $('#moduleurl').val();
$(document).on('click','.edit-field',function(e){
    e.preventDefault();
    popup.show();
    var id = $(this).parent().data('id');
    $$('fieldInfo').load(ajaxUrl+"action=loadData&elem="+id);
    $$("fieldInfo").setValues({
        id: id

    });
})
$(document).on('click','.save-sort',function (e) {
    e.preventDefault();
    var category = $(this).data('category')
    // alert(category)
    var group = $(this).parent().next().find('.group-item');
    // alert(group.html())
    var data = [];
    group.each(function (ind,elem) {
        var id = $(elem).data('id');
        data.push(id);
    });
    $.get(ajaxUrl, {action: "save-sort", data: JSON.stringify(data)}, function (data) {
        webix.message('Сохранено');
        updateTab(category)
    });
});
$(document).on('click','.delete-field',function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    var category = $(this).data('category');

    webix.confirm({
        title:"Удалить ",
        text:"Удалить поле?",
        ok:"Да",
        cancel:"Нет",
        callback:function (result) {
            if(result){
                $.get(ajaxUrl, {action: "delete-field", item: id}, function (data) {
                    data = JSON.parse(data);
                    if(data['status']){
                        webix.message(data['msg']);
                        updateTab(category)
                    }
                    else{
                        webix.message('Произошла ошибка')
                    }
                })
            }
        }
    })

});