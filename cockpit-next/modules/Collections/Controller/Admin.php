<?php

namespace Collections\Controller;


class Admin extends \Cockpit\AuthController {


    public function index() {

        return $this->render('collections:views/index.php');
    }

    public function collection($name = null) {

        $collection = [ 'name' => '', 'label' => '', 'color' => '', 'fields'=>[], 'sortable' => false, 'in_menu' => false ];

        if ($name) {

            $collection = $this->module('collections')->collection($name);

            if (!$collection) {
                return false;
            }
        }

        // get field templates
        $templates = [];

        foreach ($this->app->helper("fs")->ls('*.php', 'collections:fields-templates') as $file) {
            $templates[] = include($file->getRealPath());
        }

        foreach ($this->app->module("collections")->collections() as $col) {
            $templates[] = $col;
        }

        return $this->render('collections:views/collection.php', compact('collection', 'templates'));
    }

    public function entries($collection) {

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) {
            return false;
        }

        $count = $this->module('collections')->count($collection['name']);

        $collection = array_merge([
            'sortable' => false
        ], $collection);

        $view = 'collections:views/entries.php';

        if ($override = $this->app->path('config:collections/'.$collection['name'].'views/entries.php')) {
            $view = $path;
        }

        return $this->render($view, compact('collection', 'count'));
    }

    public function entry($collection, $id = null) {

        $collection = $this->module('collections')->collection($collection);
        $entry      = new \ArrayObject([]);

        if (!$collection) {
            return false;
        }

        if ($id) {

            $entry = $this->module('collections')->findOne($collection['name'], ['_id' => $id]);

            if (!$entry) {
                return false;
            }
        }

        $view = 'collections:views/entry.php';

        if ($override = $this->app->path('config:collections/'.$collection['name'].'views/entry.php')) {
            $view = $override;
        }

        return $this->render($view, compact('collection', 'entry'));
    }

    public function export($collection) {

        if (!$this->app->module("cockpit")->hasaccess("collections", 'manage.collections')) {
            return false;
        }

        $collection = $this->module('collections')->collection($collection);

        if (!$collection) return false;

        $entries = $this->module('collections')->find($collection['name']);

        return json_encode($entries, JSON_PRETTY_PRINT);
    }

    public function find() {

        $collection = $this->app->param('collection');
        $options    = $this->app->param('options');

        if (!$collection) return false;

        $collection = $this->app->module('collections')->collection($collection);

        if (isset($options['filter']) && is_string($options['filter'])) {
            $options['filter'] = $this->_filter($options['filter'], $collection);
        }

        $entries = $this->app->module('collections')->find($collection['name'], $options);
        $count   = $this->app->module('collections')->count($collection['name'], isset($options['filter']) ? $options['filter'] : []);
        $pages   = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page    = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        return compact('entries', 'count', 'pages', 'page');
    }

    protected function _filter($filter, $collection) {

        if ($this->app->storage->type == 'mongolite') {
            return $this->_filterLight($filter, $collection);
        }

        if ($this->app->storage->type == 'mongodb') {
            return $this->_filterMongo($filter, $collection);
        }

        return null;

    }

    protected function _filterLight($filter, $collection) {

        $allowedtypes = ['text','longtext','boolean','select','html','wysiwyg','markdown','code'];
        $criterias    = [];
        $_filter      = null;

        foreach($collection['fields'] as $field) {

            if ($field['type'] != 'boolean' && in_array($field['type'], $allowedtypes)) {
                $criteria = [];
                $criteria[$field['name']] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

            if ($field['type']=='collectionlink') {
                $criteria = [];
                $criteria[$field['name'].'.display'] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

            if ($field['type']=='location') {
                $criteria = [];
                $criteria[$field['name'].'.address'] = ['$regex' => $filter];
                $criterias[] = $criteria;
            }

            if ($field['type']=='tags') {
                $criteria = [];
                $criteria[$field['name']] = ['$all' => [$filter]];
                $criterias[] = $criteria;
            }

        }

        if (count($criterias)) {
            $_filter = ['$or' => $criterias];
        }

        return $_filter;
    }

    protected function _filterMongo($filter, $collection) {

        $allowedtypes = ['text','longtext','boolean','select','html','wysiwyg','markdown','code'];
        $criterias    = [];
        $_filter      = null;

        foreach($collection['fields'] as $field) {

            if ($field['type'] != 'boolean' && in_array($field['type'], $allowedtypes)) {
                $criteria = [];
                $criteria[$field['name']] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

            if ($field['type']=='collectionlink') {
                $criteria = [];
                $criteria[$field['name'].'.display'] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

            if ($field['type']=='location') {
                $criteria = [];
                $criteria[$field['name'].'.address'] = ['$regex' => $filter, '$options' => 'i'];
                $criterias[] = $criteria;
            }

            if ($field['type']=='tags') {
                $criteria = [];
                $criteria[$field['name']] = ['$all' => [$filter]];
                $criterias[] = $criteria;
            }

        }

        if (count($criterias)) {
            $_filter = ['$or' => $criterias];
        }

        return $_filter;
    }
}
