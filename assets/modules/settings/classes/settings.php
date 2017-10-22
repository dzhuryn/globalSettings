<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14.06.2017
 * Time: 8:43
 */
class settings
{
    public $S;
    public $SC;
    public $SS;

    /* @var DocumentParser */
    public $modx;

    public function __construct($modx)
    {
        $this->modx = $modx;
        $this->S = $this->modx->getFullTableName('module_settings');
        $this->SC = $this->modx->getFullTableName('module_settings_category');
        $this->SS = $this->modx->getFullTableName('system_settings');
    }
    public function firstStart()
    {
        global $modx;
        $sql = " CREATE TABLE IF NOT EXISTS " . $modx->getFullTableName('module_settings_category') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `caption` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        $modx->db->query($sql);
        $sql = " CREATE TABLE IF NOT EXISTS " . $modx->getFullTableName('module_settings') . " (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `index` int(11) DEFAULT '4',
  `description` varchar(255) DEFAULT NULL,
  `elements` tinytext,
  `category` int(11) DEFAULT NULL,
  `type` varchar(15) DEFAULT NULL,
  `value` text,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
        $modx->db->query($sql);

        $count = $modx->db->getValue($modx->db->query("select id from ".$modx->getFullTableName('module_settings_category')." where `caption` = 'Без категории'"));
        if(empty($count)){
            $sql = "INSERT INTO " . $modx->getFullTableName('module_settings_category') . " (`caption`) VALUES ('Без категории');";
            $modx->db->query($sql);
        }
    }

    public function getCategory()
    {

        $sql = "select * from $this->SC";
        $q = $this->modx->db->query($sql);
        $res = $this->modx->db->makeArray($q);
        foreach ($res as $re) {
            $data[] = [
                'id' => $re['id'],
                'value' => $re['caption'],
            ];
        }

        return $data;

    }

    public function saveField($data)
    {
        $newCategory = false;
        foreach ($data as $key => $val) {
            $data[$key] = $this->modx->db->escape($val);
        }

        if (empty($data['name'])) {
            return json_encode(['status' => false, "text" => "Название не заполнено"]);
        }

        $resp = $this->searchField($data['name']);
        if (empty($data['id']) && !empty($resp)) {
            return json_encode(['status' => 0, "text" => "Поле существует"]);
        }

        if (!empty($data['newCategory'])) {
            $data['category'] = $this->createCategory($data['newCategory']);
            $newCategory = true;
        }
        $sql = "select count(id) from $this->S where `category`=" . intval($data['category']);
        $q = $this->modx->db->query($sql);
        $count = $this->modx->db->getValue($q);
        if ($count == 0) {
            $newCategory = true;
        }
        unset($data['newCategory']);

        if (empty($data['id'])) {
            $fieldId = $this->modx->db->insert($data, $this->S);
        } else {
            $fieldId = $this->modx->db->update($data, $this->S, "id = " . intval($data['id']));

        }
        if (empty($fieldId)) {
            return json_encode(['status' => 0, "text" => "Сохранить не удалось"]);
        } else if ($newCategory == true) {
            return json_encode(['status' => 1, "text" => "Сохранено"]);
        } else {
            return json_encode(['status' => 2, 'category' => $data['category'], "text" => "Сохранено"]);
        }
    }

    public function searchField($name)
    {
        return $this->modx->db->getValue($this->modx->db->query("select * from $this->S where `name` = '$name'"));
    }

    public function createCategory($newCategory)
    {

        $categoryId = $this->modx->db->getValue($this->modx->db->query("select id from $this->SC where `caption` = '$newCategory'"));

        if (empty($categoryId)) {
            $categoryId = $this->modx->db->insert(['caption' => $newCategory], $this->SC);

        }
        return $categoryId;
    }

    public function render()
    {
        $sql = "select s.*,sc.caption as category,sc.id as categoryId from $this->S as s,$this->SC as sc where s.category = sc.id order by sc.id";
        $q = $this->modx->db->query($sql);
        $fields = $this->modx->db->makeArray($q);

        $groups = [];
        $renderFields = [];

        foreach ($fields as $field) {
            $groups[$field['categoryId']] = $field['category'];
            $renderFields[$field['categoryId']][] = $this->renderField($field);
        }

        $tabs = [];
        $forms = [];

        foreach ($groups as $id => $group) {
            $tabs[] = [
                'header' => $group,

                'body' => [
                    'id' => 'formView_' . $id,
                    'view' => 'htmlform',
                    'complexData' => true,
                    'scroll' => true,
                    'on' => [
                        'onAfterRender' => 'test'
                    ],
                    'template' => "http->[+moduleurl+]action=getForm&group=" . $id,

                ]
            ];
            //$forms[] = '<div id="area_' . $id . '"><form id="mutate">' . implode('', $renderFields[$id]) . '</form></div>';
        }


        $tabs[] = [
            'header' => 'Редактировать',

            'body' => [
                'id' => 'formView_edit',
                'view' => 'htmlform',
                'complexData' => true,
                'scroll' => true,
                'on' => [
                    'onAfterRender' => 'test'
                ],
                'template' => "http->[+moduleurl+]action=getForm&group=edit",

            ]
        ];
        $tabs = json_encode($tabs);

        return [
            'tabs' => $tabs,
            'forms' => implode(',', $forms),
        ];

    }

    public function renderField($field)
    {
        $output = '';
        switch ($field['type']) {
            case 'text':
                $output = '<div class="row">
                <div class="col-md-2">
                    <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
                </div>
                <div class="col-md-10">
                    <input type="text" class="form-control" name="' . $field['name'] . '" value="' . $field['value'] . '">
                </div>
            </div>';
                break;
            case 'textarea':
                $output = '<div class="row">
                <div class="col-md-2">
                    <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
                </div>
                <div class="col-md-10">
                <textarea class="form-control" name="' . $field['name'] . '" >' . $field['value'] . '</textarea>
                    
                </div>
            </div>';
                break;
            case 'richtext':
                $output = '<div class="row">
                <div class="col-md-2">
                    <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
                </div>
                <div class="col-md-10">
                <textarea class="richtext form-control" name="' . $field['name'] . '" >' . $field['value'] . '</textarea>   
                </div>
            </div>';
                break;
            case 'checkbox':
                $boxes = '';
                $boxesData = $this->renderElements($field['elements']);

                $values = [];
                if (!empty($field['value'])) {
                    $values = explode('||', $field['value']);
                }

                foreach ($boxesData as $value => $caption) {
                    $checked = '';
                    if (in_array($value, $values)) {
                        $checked = ' checked';
                    }
                    $boxes .= '<label class="checkbox-inline"><input' . $checked . ' name="' . $field['name'] . '" type="checkbox" value="' . $value . '">' . $caption . '</label>';
                }
                $output = '<div class="row">
                <div class="col-md-2">
                    <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
                </div>
                <div class="col-md-10">
                    ' . $boxes . '
                </div>
            </div>';
                break;
            case 'select':
                $options = '';
                $data = $this->renderElements($field['elements']);
                foreach ($data as $value => $caption) {
                    $selected = '';
                    if (!empty($field['value']) && $field['value'] == $value) {
                        $selected = 'selected';
                    }
                    $options .= '<option ' . $selected . ' value="' . $value . '">' . $caption . '</option>';
                }
                $output = '<div class="row">
                <div class="col-md-2">
                    <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
                </div>
                <div class="col-md-10">
                    <select name="' . $field['name'] . '" class="form-control">
                    ' . $options . '
</select>
                </div>
            </div>';
                break;
            case 'image':
                $src = '';
                if (!empty($field['value'])) {
                    $src = 'src = "' . $field['value'] . '" ';
                }
                $output = '<div class="row">
    <div class="col-md-2">
        <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
    </div>
    <div class="col-md-10">
        <div class="input-group">
            <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" value="' . $field['value'] . '">
            <span class="input-group-btn">
    <button class="btn btn-primary" type="button" onclick="BrowseServer(\'' . $field['name'] . '\')">Выбрать картинку</button>
  </span>
 
        </div>
         <img ' . $src . ' id="' . $field['name'] . '_image" src="" style="max-width: 300px; max-height: 300px; margin: 4px 0px; cursor: pointer;">
    </div>
</div>';
                break;
            case 'file':
                $output = '<div class="row">
    <div class="col-md-2">
        <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
    </div>
    <div class="col-md-10">
        <div class="input-group">
            <input type="text" class="form-control" name="' . $field['name'] . '" id="' . $field['name'] . '" value="' . $field['value'] . '">
            <span class="input-group-btn">
    <button class="btn btn-primary" type="button" onclick="BrowseFileServer(\'' . $field['name'] . '\')">Выбрать файл</button>
  </span>
        </div>
    </div>
</div>';
                break;
            case 'multitv':
                $output = '
<div class="row">
                <div class="col-md-2">
                    <span><b>' . $field['name'] . '</b></span><br><span>' . $field['description'] . '</span>
                </div>
                <div class="col-md-10 multitv">
                ' . $this->renderMtv($field) . '
                </div>
            </div>';
                break;
        }

        return $output;
    }

    public function renderElements($element)
    {

        $out = array();
        if (stristr($element, "@SELECT")) {
            //$prefix = str_replace('site_content','',$this->modx->getFullTableName('site_content'))

            //echo $element;
            $sql = str_replace(['@', '[+PREFIX+]'], ['', $this->modx->db->config['table_prefix']], $element);
            $q = $this->modx->db->query($sql);
            $res = $this->modx->db->makeArray($q);
            if (empty($res)) {
                return [];
            } else {
                $valueKey = array_keys($res[0])[0];
                $idKey = array_keys($res[0])[1];

                $data = [];
                foreach ($res as $re) {
                    $data[$re[$idKey]] = $re[$valueKey];
                }
                return $data;
            }


        }
        if (stristr($element, "@EVAL")) {
            $element = trim(substr($element, 6));
            $element = str_replace("\$modx->", "\$this->modx->", $element);
            $element = eval($element);
        }
        if ($element != '') {
            $tmp = explode("||", $element);
            foreach ($tmp as $v) {
                $tmp2 = explode("==", $v);
                $key = isset($tmp2[1]) && $tmp2[1] != '' ? $tmp2[1] : $tmp2[0];
                $value = $tmp2[0];
                if ($key != '') {
                    $out[$key] = $value;
                }
            }
        }
        return $out;
    }

    public function renderMtv($field)
    {
        define('MTV_PATH', '/assets/tvs/multitv/');
        define('MTV_BASE_PATH', MODX_BASE_PATH . MTV_PATH);
        if (!class_exists('multiTV')) {
            include MTV_BASE_PATH . 'includes/multitv.class.php';
        }

        $multiTV = new multiTV($this->modx, array(
                'type' => 'tv',
                'tvDefinitions' => [
                    'name' => $field['name'],
                    'id' => $field['id'],
                    'caption' => $field['description'],
                    'value' => $field['value'],

                ],
                'tvUrl' => MTV_PATH
            )
        );
        return $multiTV->generateScript();


    }

    public function saveValues($resp)
    {


        $data = [];

        foreach ($resp as $key => $item) {
            $name = $item['name'];
            $value = $item['value'];

            if (strpos($name, '_mtv') === false) {
                if (strpos($value, '{"fieldValue":[{') === false) {
                    if (empty($data[$name])) {
                        $data[$name] = $value;
                    } else {
                        $data[$name] .= '||' . $value;
                    }

                } else {

                    $id = intval(str_replace('tv', '', $name));
                    $name = $this->modx->db->getValue($this->modx->db->query("select name from $this->S where `id` = '$id'"));

                    $data[$name] = $value;
                }

            }
        }


        foreach ($data as $name => $value) {
            $name = $this->modx->db->escape($name);
            $valueEscape = $this->modx->db->escape($value);

            $this->modx->db->update([
                'value' => $value
            ], $this->S, 'name = "' . $name . '"');


            $name = 'g_'.$name;
            $resp = $this->modx->db->getValue($this->modx->db->query("select setting_name from $this->SS where `setting_name` = '$name'"));
            $fields = [
                'setting_name'=>$name,
                'setting_value'=>$valueEscape,
            ];

            //для настроек убираем обертку fieldValue чтобы использовать параметр fromJson для multitv

            if (strpos($value, '{"fieldValue":[{') !== false) {
                $value = json_decode($value,true);
                if(!empty($value['fieldValue'])){
                    $value = $value['fieldValue'];
                }
                $value = json_encode($value);
                $valueEscape = $this->modx->db->escape($value);
                $fields['setting_value'] = $valueEscape;
            }

            if(empty($resp)){
                $this->modx->db->insert($fields,$this->SS);
            }
            else{
                $resp = $this->modx->db->update($fields,$this->SS,'setting_name = "'.$name.'"');

            }
        }


    }

    public function getForm()
    {
        if ($_GET['group'] == 'edit') {
            $group = 'edit';
            return $this->renderEditTab();

        } else {
            $group = intval($_GET['group']);
        }

        $sql = "select s.*,sc.caption as category,sc.id as categoryId from $this->S as s,$this->SC as sc where s.category = $group and s.category = sc.id order by s.index asc";
        $q = $this->modx->db->query($sql);
        $fields = $this->modx->db->makeArray($q);

        $groups = [];
        $renderFields = [];

        if (is_array($fields)) {
            foreach ($fields as $field) {
                $groups[$field['categoryId']] = $field['category'];
                $renderFields[$field['categoryId']][] = $this->renderField($field);
            }
        }

        $tabs = [];
        $forms = [];
        foreach ($groups as $id => $group) {

            $tabs[] = [
                'header' => $group,

                'body' => [
                    'id' => 'formView_' . $id,
                    'view' => 'htmlform',
                    'scroll' => true,
                    'template' => "http->[+moduleurl+]?action=getForm&group=" . $id,

                ]
            ];
            $forms[] = '<div id="area_' . $id . '"><form id="mutate">' . implode('', $renderFields[$id]) . '</form></div>';
        }


        $tabs = json_encode($tabs);

        return implode(',', $forms);
//
    }

    private function renderEditTab()
    {
        $output = '';
        $modx = $this->modx;
        $sql = "select * from $this->SC";
        $q = $modx->db->query($sql);
        $groups = $modx->db->makeArray($q);
        //var_dump($groups);

        foreach ($groups as $group) {
            $sql = "select * from $this->S where category = " . intval($group['id']) . " ORDER BY `index` asc";
            $q = $modx->db->query($sql);
            $fields = $modx->db->makeArray($q);
            if (count($fields) == 0) {
                continue;
            }
            $output .= '<div class="group-title">' . $group['caption'] . '  <a href="#" class="save-sort" data-category="' . $group['id'] . '">Сохранить последовательность</a></div>';
            $output .= '<div class="group field-group">';
            foreach ($fields as $field) {
                $output .= '
<div class="group-item" data-id="' . $field['id'] . '">
<a href="#" class="edit-field">' . $field['name'] . ' (' . $field['description'] . ')</a> <a data-category="' . $field['category'] . '" data-id="' . $field['id'] . '" href="#" class="delete-field">x</a>
</div>
';
            }
            $output .= '</div>';
        }
        return '<div class="edit-field-tab">' . $output . '</div>';
    }

    public function deleteField($id)
    {
        $id = intval($id);
        $sql = "delete from $this->S where id = " . $id;
        $this->modx->db->query($sql);
        return ['status' => true, 'msg' => 'Удалено'];
    }

    public function saveSorting($data)
    {
        $data = json_decode($data, true);
        if (is_array($data)) {
            foreach ($data as $key => $id) {
                $key = intval($key);
                $id = intval($id);
                $sql = "update $this->S set `index`=$key where id = $id";
                $this->modx->db->query($sql);
            }
        };
    }

    public function loadData($id)
    {
        $id = intval($id);
        $sql = "select * from $this->S where id = $id";
        $q = $this->modx->db->query($sql);;
        $data = $this->modx->db->getRow($q);
        return $data;
    }


}