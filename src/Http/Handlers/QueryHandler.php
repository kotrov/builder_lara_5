<?php

namespace Vis\Builder\Handlers;

use Vis\Builder\JarboeController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

/**
 * Class QueryHandler.
 */
class QueryHandler
{
    /**
     * @var JarboeController
     */
    protected $controller;
    /**
     * @var
     */
    protected $dbName;
    /**
     * @var
     */
    protected $dbOptions;
    /**
     * @var
     */
    protected $model;
    /**
     * @var mixed
     */
    protected $definition;
    /**
     * @var mixed
     */
    protected $definitionName;
    /**
     * @var
     */
    protected $db;
    /**
     * @var
     */
    protected $extendsTable;
    /**
     * @var
     */
    protected $extendsTableId;
    /**
     * @var array
     */
    protected $extendsFields = [];

    /**
     * QueryHandler constructor.
     * @param JarboeController $controller
     */
    public function __construct(JarboeController $controller)
    {
        $this->controller = $controller;
        $this->definition = $controller->getDefinition();
        $this->definitionName = $controller->getOption('def_name');

        if (isset($this->definition['cache']['tags'])) {
            $this->cache = $this->definition['cache']['tags'];
        } else {
            $this->cache = '';
        }

        $this->dbOptions = $this->definition['db'];
        $this->model = $this->definition['options']['model'];
        $this->dbName = $this->definition['db']['table'];

        if (isset($this->definition['options']['extends']) && count($this->definition['options']['extends'])) {
            foreach ($this->definition['options']['extends'] as $extend) {
                $table = $extend['table'];
                $this->extendsTable[$table] = $table;

                if (isset($extend['id'])) {
                    $this->extendsTableId[$table] = $extend['id'];
                } else {
                    $this->extendsTableId[$table] = $this->dbName.'_id';
                }
            }
        }
    }

    /**
     * @param $ident
     * @return mixed
     */
    protected function getOptionDB($ident)
    {
        return $this->dbOptions[$ident];
    }

    /**
     * @param $ident
     * @return bool
     */
    protected function hasOptionDB($ident)
    {
        return isset($this->dbOptions[$ident]);
    }

    /**
     * @param bool $isPagination
     * @param bool $isUserFilters
     * @param array $betweenWhere
     * @param bool $isSelectAll
     * @return mixed
     */
    public function getRows($isPagination = true, $isUserFilters = true, $betweenWhere = [], $isSelectAll = false)
    {
        $modelName = $this->model;
        $this->db = new $modelName();

        $this->prepareSelectValues();

        if ($isSelectAll) {
            $this->db->addSelect($this->dbName.'.*');
        }

        $this->prepareFilterValues();

        if ($isUserFilters) {
            $this->onSearchFilterQuery();
        }

        if ($this->extendsTable) {
            foreach ($this->extendsTable as $table) {
                $this->db->leftJoin($table, "{$table}.{$this->extendsTableId[$table]}", '=', "{$this->dbName}.id");
            }
        }

        $this->dofilter();

        $sessionPath = 'table_builder.'.$this->definitionName.'.order';
        $order = Session::get($sessionPath, []);

        if ($order && $isUserFilters) {
            $this->db->orderBy($this->dbName.'.'.$order['field'], $order['direction']);
        } elseif ($this->hasOptionDB('order')) {
            $order = $this->getOptionDB('order');

            foreach ($order as $field => $direction) {
                $this->db->orderBy($this->dbName.'.'.$field, $direction);
            }
        }

        if ($betweenWhere) {
            $betweenField = $betweenWhere['field'];
            $betweenValues = $betweenWhere['values'];

            $this->db->whereBetween($betweenField, $betweenValues);
        }

        if ($this->hasOptionDB('pagination') && $isPagination) {
            $pagination = $this->getOptionDB('pagination');
            $perPage = $this->getPerPageAmount($pagination['per_page']);

            return $this->db->paginate($perPage);
        }

        return $this->db->get();
    }

    private function dofilter()
    {
        if (Input::has('filter')) {
            $filters = request('filter');

            foreach ($filters as $nameField => $valueField) {
                if ($valueField) {
                    $this->db->where($nameField, $valueField);
                }
            }
        }
    }

    /**
     * @param $info
     * @return mixed
     */
    public function getPerPageAmount($info)
    {
        if (! is_array($info)) {
            return $info;
        }

        $sessionPath = 'table_builder.'.$this->definitionName.'.per_page';
        $perPage = Session::get($sessionPath);

        if (! $perPage) {
            $keys = array_keys($info);
            $perPage = $keys[0];
        }

        return $perPage;
    }

    protected function prepareFilterValues()
    {
        $filters = isset($this->definition['filters']) ? $this->definition['filters'] : [];
        if (is_callable($filters)) {
            $filters($this->db);

            return;
        }

        foreach ($filters as $name => $field) {
            $this->db->where($name, $field['sign'], $field['value']);
        }
    }

    /**
     * @param $values
     */
    protected function doPrependFilterValues(&$values)
    {
        $filters = isset($this->definition['filters']) ? $this->definition['filters'] : [];
        if (is_callable($filters)) {
            return;
        }

        foreach ($filters as $name => $field) {
            $values[$name] = $field['value'];
        }
    }

    protected function prepareSelectValues()
    {
        $this->db = $this->db->select($this->dbName.'.id');

        if (isset($this->definition['options']['is_sortable']) && $this->definition['options']['is_sortable']) {
            $this->db = $this->db->addSelect($this->dbName.'.priority');
        }

        $fields = $this->controller->getFields();

        foreach ($fields as $field) {
            $field->onSelectValue($this->db);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|object
     */
    public function getRow($id)
    {
        $this->db = DB::table($this->dbName);

        if ($this->extendsTable) {
            foreach ($this->extendsTable as $table) {
                $this->db->leftJoin($table, "{$table}.{$this->extendsTableId[$table]}", '=', "{$this->dbName}.id");
            }
        }

        $this->prepareSelectValues();

        $this->db->where($this->dbName.'.id', $id);

        return $this->db->first();
    }

    protected function onSearchFilterQuery()
    {
        $sessionPath = 'table_builder.'.$this->definitionName.'.filters';

        $filters = Session::get($sessionPath, []);
        foreach ($filters as $name => $value) {
            if ($this->controller->hasCustomHandlerMethod('onSearchFilter')) {
                $res = $this->controller->getCustomHandler()->onSearchFilter($this->db, $name, $value);
                if ($res) {
                    continue;
                }
            }

            $this->controller->getField($name)->onSearchFilter($this->db, $value);
        }
    }

    /**
     * @param $values
     * @return array|mixed
     */
    public function updateRow($values)
    {
        $this->clearCache();

        if ($this->controller->hasCustomHandlerMethod('handleUpdateRow')) {
            $res = $this->controller->getCustomHandler()->handleUpdateRow($values);
            if ($res) {
                return $res;
            }
        }

        $updateData = $this->getRowQueryValues($values);
        $updateDataRes = [];

        $model = $this->model;
        $this->checkFields($updateData);

        if ($this->controller->hasCustomHandlerMethod('onUpdateRowData')) {
            $this->controller->getCustomHandler()->onUpdateRowData($updateData, $values);
        }
        $this->doValidate($updateData);

        $modelObj = $model::find($values['id']);

        if (method_exists($modelObj, 'setFillable')) {
            $modelObj->setFillable(array_keys($updateData));
        }

        foreach ($updateData as $field => $data) {
            if (isset($this->definition['fields'][$field]) && ! isset($this->definition['fields'][$field]['tabs'])) {
                $updateDataRes[$field] = $this->getData($data);
            } else {
                $this->getDataTabs($updateDataRes, $updateData, $field);
            }
        }

        $modelObj->update($updateDataRes);

        foreach ($this->controller->getPatterns() as $pattern) {
            $pattern->update($values, $values['id']);
        }

        $fields = $this->controller->getFields();
        foreach ($fields as $field) {
            if (preg_match('~^many2many~', $field->getFieldName())) {
                $this->onManyToManyValues($field->getFieldName(), $values, $values['id']);
            }

            $this->updateGroupIfUseTable($field, $values['id']);
        }

        $this->updateExtendsTable($values['id']);

        $res = [
            'id'     => $values['id'],
            'values' => $updateData,
        ];
        if ($this->controller->hasCustomHandlerMethod('onUpdateRowResponse')) {
            $this->controller->getCustomHandler()->onUpdateRowResponse($res);
        }

        return $res;
    }

    /**
     * @param $data
     * @return false|string
     */
    private function getData($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }

        return $data;
    }

    /**
     * @param $updateDataRes
     * @param $updateData
     * @param $field
     */
    private function getDataTabs(&$updateDataRes, $updateData, $field)
    {
        if (isset($this->definition['fields'][$field]['tabs'])) {
            foreach ($this->definition['fields'][$field]['tabs'] as $tab) {
                $updateDataRes[$field.$tab['postfix']] = $this->getTabsDataField($updateData, $field, $tab);
            }
        }
    }

    /**
     * @param $updateData
     * @param $field
     * @param $tab
     * @return string
     */
    private function getTabsDataField($updateData, $field, $tab)
    {
        if ($updateData[$field.$tab['postfix']]) {
            return $updateData[$field.$tab['postfix']];
        }

        if ($updateData[$field] && $this->definition['fields'][$field]['type'] != 'image') {
            $translateText = $this->generateTranslation($updateData[$field], ltrim($tab['postfix'], '_'));

            return $translateText ?: '';
        }

        return '';
    }

    /**
     * @param $phrase
     * @param $thisLang
     * @return mixed
     */
    private function generateTranslation($phrase, $thisLang)
    {
        try {
            $langsDef = config('translations.config.def_locale');

            $lang = str_replace('ua', 'uk', $thisLang);
            $langsDef = str_replace('ua', 'uk', $langsDef);

            $translator = new \Yandex\Translate\Translator(config('builder.translate_cms.api_yandex_key'));
            $translation = $translator->translate($phrase, $langsDef.'-'.$lang);

            if (isset($translation->getResult()[0])) {
                return $translation->getResult()[0];
            }
        } catch (\Yandex\Translate\Exception $e) {
        }
    }

    /**
     * @param $field
     * @param $id
     */
    private function updateGroupIfUseTable($field, $id)
    {
        if ($field->getAttribute('use_table') && $field->getAttribute('type') == 'group') {
            $nameField = $field->getFieldName();
            $group = request($nameField);
            $tableUse = $field->getAttribute('use_table')['table'];
            $fieldForeign = $field->getAttribute('use_table')['id'];
            DB::table($tableUse)->where($fieldForeign, $id)->delete();

            foreach ($group as $name => $arrayValue) {
                foreach ($arrayValue as $k => $item) {
                    $resultArray[$k][$fieldForeign] = $id;
                    $resultArray[$k][$name] = $item;
                }
            }

            if (isset($resultArray)) {
                DB::table($tableUse)->insert($resultArray);
            }
        }
    }

    /**
     * @param $id
     */
    public function updateExtendsTable($id)
    {
        if (count($this->extendsFields)) {
            foreach ($this->extendsFields as $tableEx => $fields) {
                $table = DB::table($tableEx);

                $hasExistRecord = DB::table($tableEx)->where($this->extendsTableId[$tableEx], $id)->count();
                if ($hasExistRecord) {
                    $table->where($this->extendsTableId[$tableEx], $id)->update($fields);
                } else {
                    $fields[$this->extendsTableId[$tableEx]] = $id;
                    $table->insert($fields);
                }
            }
        }
    }

    /**
     * @param $id
     * @return array
     */
    public function cloneRow($id)
    {
        $this->clearCache();

        if ($this->controller->hasCustomHandlerMethod('handleCloneRow')) {
            $res = $this->controller->getCustomHandler()->handleCloneRow($id);
            if ($res) {
                return $res;
            }
        }
        $this->db = DB::table($this->dbName);
        $page = (array) $this->db->where('id', $id)->select('*')->first();
        Event::fire('table.clone', [$this->dbName, $id]);

        unset($page['id']);

        $newId = $this->db->insertGetId($page);

        $this->cloneExtendsTables($id, $newId);

        return [
            'id'     => $id,
            'status' => $page,
        ];
    }

    /**
     * @param $id
     * @param $newId
     */
    private function cloneExtendsTables($id, $newId)
    {
        if (isset($this->extendsTable) && count($this->extendsTable)) {
            foreach ($this->extendsTable as $table) {
                $page = (array) DB::table($table)->where($this->extendsTableId[$table], $id)->select('*')->first();
                unset($page['id']);
                $page[$this->extendsTableId[$table]] = $newId;
                DB::table($table)->insertGetId($page);
            }
        }
    }

    /**
     * @param $id
     * @return array|mixed
     */
    public function deleteRow($id)
    {
        $this->clearCache();

        if ($this->controller->hasCustomHandlerMethod('handleDeleteRow')) {
            $res = $this->controller->getCustomHandler()->handleDeleteRow($id);
            if ($res) {
                return $res;
            }
        }

        foreach ($this->controller->getPatterns() as $pattern) {
            $pattern->delete($id);
        }

        $res = $this->model::find($id)->delete();

        $res = [
            'id'     => $id,
            'status' => $res,
        ];

        $this->deleteExtendsTables($id);

        if ($this->controller->hasCustomHandlerMethod('onDeleteRowResponse')) {
            $this->controller->getCustomHandler()->onDeleteRowResponse($res);
        }

        return $res;
    }

    /**
     * @param $id
     */
    private function deleteExtendsTables($id)
    {
        if (isset($this->extendsTable) && count($this->extendsTable)) {
            foreach ($this->extendsTable as $table) {
                DB::table($table)->where($this->extendsTableId[$table], $id)->delete();
            }
        }
    }

    /**
     * @param $input
     */
    public function fastSave($input)
    {
        $this->clearCache();

        $nameField = $input['name'];

        if (isset($input['value'])) {
            $valueField = $input['value'];
        } else {
            $fieldArray = request($nameField) ?? [];
            $valueField = json_encode(array_values($fieldArray));
        }

        $modelObj = $this->model::find($input['id']);
        $modelObj->$nameField = $valueField;

        $modelObj->save();
    }

    /**
     * @param $values
     * @return array|mixed
     */
    public function insertRow($values)
    {
        $this->clearCache();
        $insertDataRes = [];

        if ($this->controller->hasCustomHandlerMethod('handleInsertRow')) {
            $res = $this->controller->getCustomHandler()->handleInsertRow($values);
            if ($res) {
                return $res;
            }
        }

        $insertData = $this->getRowQueryValues($values);

        $this->checkFields($insertData);

        $this->doValidate($insertData);
        $id = false;
        if ($this->controller->hasCustomHandlerMethod('onInsertRowData')) {
            $id = $this->controller->getCustomHandler()->onInsertRowData($insertData);
        }

        if (! $id) {
            foreach ($insertData as $field => $data) {
                if (isset($this->definition['fields'][$field]) && ! isset($this->definition['fields'][$field]['tabs'])) {
                    $insertDataRes[$field] = $this->getData($data);
                } else {
                    $this->getDataTabs($insertDataRes, $insertData, $field);
                }
            }

            $modelThis = $this->model;

            $objectModel = new $modelThis();

            foreach ($insertDataRes as $key => $value) {
                $objectModel->$key = $value;
            }

            $objectModel->save();
            $id = $objectModel->id;
        }

        foreach ($this->controller->getPatterns() as $pattern) {
            $pattern->insert($values, $id);
        }

        $fields = $this->controller->getFields();
        foreach ($fields as $field) {
            if (preg_match('~^many2many~', $field->getFieldName())) {
                $this->onManyToManyValues($field->getFieldName(), $values, $id);
            }
            $this->updateGroupIfUseTable($field, $id);
        }

        $this->updateExtendsTable($id);

        $res = [
            'id' => $id,
            'values' => $insertData,
        ];

        if ($this->controller->hasCustomHandlerMethod('onInsertRowResponse')) {
            $this->controller->getCustomHandler()->onInsertRowResponse($res);
        }

        return $res;
    }

    /**
     * @param $ident
     * @param $values
     * @param $id
     */
    private function onManyToManyValues($ident, $values, $id)
    {
        $field = $this->controller->getField($ident);
        $vals = isset($values[$ident]) ? $values[$ident] : [];
        $field->onPrepareRowValues($vals, $id);
    }

    /**
     * @param $values
     */
    private function doValidate($values)
    {
        $errors = [];
        $fields = $this->definition['fields'];

        foreach ($fields as $ident => $options) {
            try {
                $field = $this->controller->getField($ident);
                if ($field->isPattern()) {
                    continue;
                }

                $tabs = $field->getAttribute('tabs');
                if ($tabs) {
                    if (! $field->getAttribute('extends_table')) {
                        foreach ($tabs as $tab) {
                            $fieldName = $ident.$tab['postfix'];
                            $field->doValidate($values[$fieldName]);
                        }
                    }
                } else {
                    if (array_key_exists($ident, $values)) {
                        $field->doValidate($values[$ident]);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = 'Поле "'.$field->getAttribute('caption').'" '.$e->getMessage();
                continue;
            }
        }

        if ($errors) {
            $errors = implode('<br>', $errors);

            throw new \RuntimeException($errors);
        }
    }

    /**
     * @param $values
     * @return mixed
     */
    private function getRowQueryValues($values)
    {
        $values = $this->unsetFutileFields($values);

        $fields = $this->definition['fields'];

        foreach ($fields as $ident => $options) {
            $field = $this->controller->getField($ident);

            if ($field->isPattern()) {
                continue;
            }

            $tabs = $field->getAttribute('tabs');
            if ($tabs) {
                foreach ($tabs as $tab) {
                    $fieldName = $ident.$tab['postfix'];
                    $values[$fieldName] = $field->prepareQueryValue($values[$fieldName]);

                    if ($field->getAttribute('extends_table') && array_key_exists($fieldName, $values)) {
                        $this->extendsFields[$field->getAttribute('extends_table')][$fieldName] = $field->prepareQueryValue($values[$fieldName]);
                        unset($values[$fieldName]);
                        continue;
                    }
                }
            } else {
                if (array_key_exists($ident, $values)) {
                    if ($field->getAttribute('extends_table')) {
                        $this->extendsFields[$field->getAttribute('extends_table')][$ident] = $field->prepareQueryValue($values[$ident]);
                        unset($values[$ident]);
                        continue;
                    }

                    $values[$ident] = $field->prepareQueryValue($values[$ident]);

                    if ($field->getAttribute('type') == 'group' && $field->getAttribute('use_table')) {
                        unset($values[$ident]);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @param $values
     * @return mixed
     */
    private function unsetFutileFields($values)
    {
        unset($values['id']);
        unset($values['query_type']);

        foreach ($values as $key => $val) {
            if (preg_match('~^many2many~', $key)) {
                unset($values[$key]);
            }
        }

        // patterns
        unset($values['pattern']);

        // for tree
        unset($values['node']);
        unset($values['__node']);

        return $values;
    }

    /**
     * @param $values
     */
    private function checkFields(&$values)
    {
        $fields = $this->definition['fields'];

        foreach ($fields as $ident => $options) {
            $field = $this->controller->getField($ident);

            if (method_exists($field, 'getNewValueId') && isset($values[$ident.'_new_foreign'])) {
                if ($new_id = $field->getNewValueId($values[$ident.'_new_foreign'])) {
                    $values[$ident] = $new_id;
                }
                unset($values[$ident.'_new_foreign']);
            }

            if ($field->isPattern()) {
                continue;
            }

            $tabs = $field->getAttribute('tabs');

            if ($tabs) {
                foreach ($tabs as $tab) {
                    $this->checkField($values, $ident, $field);
                }
            } else {
                if (isset($values[$ident])) {
                    $this->checkField($values, $ident, $field);
                }
            }
        }
    }

    /**
     * @param $values
     * @param $ident
     * @param $field
     */
    private function checkField($values, $ident, $field)
    {
        if (! $field->isEditable()) {
            throw new \RuntimeException("Field [{$ident}] is not editable");
        }
    }

    /**
     * @return bool
     */
    public function clearOrderBy()
    {
        $sessionPath = 'table_builder.'.$this->definitionName.'.order';
        Session::forget($sessionPath);

        return true;
    }

    public function clearCache()
    {
        if (isset($this->definition['cache'])) {
            $cache = $this->definition['cache'];

            if (isset($cache['tags'])) {
                Cache::tags($cache['tags'])->flush();
            }

            if (isset($cache['keys'])) {
                Cache::forget($cache['keys']);
            }
        }
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function getUploadedFiles()
    {
        $list = File::files(public_path().'/storage/files');

        return [
            'status' => 'success',
            'data'   => view('admin::tb.files_list', compact('list'))->render(),
        ];
    }

    /**
     * @param $field
     * @return array
     */
    public function getUploadedImages($field)
    {
        if ($field->getAttribute('use_image_storage')) {
            return $this->getImagesWithImageStorage();
        }

        return $this->getImagesWithDefaultPath();
    }

    /**
     * @return array
     * @throws \Throwable
     */
    private function getImagesWithImageStorage()
    {
        if (class_exists('\Vis\ImageStorage\Image')) {
            $list = \Vis\ImageStorage\Image::orderBy('created_at', 'desc');

            if (request('tag')) {
                $list->leftJoin('vis_tags2entities', 'id_entity', '=', 'vis_images.id')->where('entity_type', 'Vis\ImageStorage\Image')->where('id_tag', request('tag'));
            }

            if (request('gallary')) {
                $list->leftJoin('vis_images2galleries', 'id_image', '=', 'vis_images.id')->where('id_gallery', request('gallary'));
            }

            if (request('q')) {
                $list->where('vis_images.title', 'like', request('q').'%');
            }

            $list = $list->groupBy('vis_images.id')->paginate(18);

            $tags = \Vis\ImageStorage\Tag::where('is_active', 1)->orderBy('title', 'asc')->get();
            $galleries = \Vis\ImageStorage\Gallery::where('is_active', 1)->orderBy('title', 'asc')->get();

            $data = [
                'status' => 'success',
                'data' => view('admin::tb.image_storage_list', compact('list', 'tags', 'galleries'))->render(),
            ];
        } else {
            $data = [
                'status' => 'success',
                'data' => 'Не подключен пакет ImageStorage',
            ];
        }

        return $data;
    }

    /**
     * @return array
     * @throws \Throwable
     */
    private function getImagesWithDefaultPath()
    {
        $files = collect(File::files(public_path('storage/editor/fotos')))->sortBy(function ($file) {
            return filemtime($file);
        })->reverse();

        $page = (int) request('page') ?: 1;
        $onPage = 24;
        $slice = $files->slice(($page - 1) * $onPage, $onPage);

        $list = new \Illuminate\Pagination\LengthAwarePaginator($slice, $files->count(), $onPage);
        $list->setPath(url()->current());

        return [
            'status' => 'success',
            'data'   => view('admin::tb.images_list', compact('list'))->render(),
        ];
    }
}
