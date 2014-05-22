<?php

class ImportArticleNodes extends ExportedDataMigration {
  protected $entityType = 'node';
  protected $bundle = 'article';
  protected $table_name = 'exported_articles';
  protected $fields = array(
    'id',
    'title',
    'body',
  );

  public function __construct() {
    parent::__construct();

    // Title field.
    $this
      ->addFieldMapping('title', 'title');

    // Body field.
    $this
      ->addFieldMapping('body', 'body')
      ->arguments(array('format' => 'filtered_html'));
  }
}
