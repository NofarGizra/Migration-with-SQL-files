<?php

class ExportedDataMigration extends Migration {

  public function __construct() {
    parent::__construct();

    // Description to be displayed on the screen when migration is in progress.
    $this->description = t('Import @bundle nodes from DB tables.', array('@bundle' => $this->bundle));

    // Create a map object for tracking the relationships between source rows.
    $id_field = empty($this->idField) ? 'id' : $this->idField;
    $key = array(
      $id_field => array(
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
      ),
    );
    $destination_handler = new MigrateDestinationEntityAPI($this->entityType, $this->bundle);
    $this->map = new MigrateSQLMap($this->machineName, $key, $destination_handler->getKeySchema($this->entityType));

    // Set the source of data. In our case it is an SQL source with the name of
    // the relevant table and fields.
    $query = db_select($this->table_name, $this->table_name)->fields($this->table_name, $this->fields);
    $this->source = new MigrateSourceSQL($query);

    // Set the destination for the import. In our case it will create nodes.
    $this->destination = new MigrateDestinationNode($this->bundle, array('text_format' => 'filtered_html'));

    // Set the node author to be the admin.
    $this
      ->addFieldMapping('uid')
      ->defaultValue(1);
  }
}

