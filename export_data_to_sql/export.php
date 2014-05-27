<?php

// This limit is used to prevent loading too many nodes at once.
$nodes_per_round = 100;

// Memory limit for the process, may be set by the user. Defaults to 500MB.
$memory_limit = drush_get_option('memory_limit', 500);

// The user may set the NID from which to start exporting. This is useful when
// the process stops due to memory limit and the user wishes to continue from
// the last successfully-exported NID.
$first_nid = drush_get_option('nid', 0);

// Last exported node ID.
$last_exported = 0;

// Count how many nodes were exported.
$count_exported = 0;

// Get the total amount of nodes that need to be exported.
$total = $query
  ->entityCondition('entity_type', 'node')
  ->entityCondition('bundle', 'article')
  ->propertyCondition('nid', $first_nid, '>=')
  ->count()
  ->execute();

// This loop should run until it gets to the "break;" statement,
// which occurs when done running through all nodes.
$i = -1;
while (TRUE) {
  $i++;

  // Get nodes of type article.
  $query = new EntityFieldQuery();
  $result = $query
    ->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'article')
    ->propertyCondition('nid', $first_nid, '>=')
    ->range($i * $nodes_per_round, $nodes_per_round)
    ->execute();

  if (empty($result['node'])) {
    // Done running through all nodes, abort script.
    break;
  }

  // Load the nodes.
  $nids = array_keys($result['node']);
  $nodes = node_load_multiple($nids);

  foreach ($nodes as $node) {
    $wrapper = entity_metadata_wrapper('node', $node);

    // Create the record.
    $body = $wrapper->body->value() ? $wrapper->body->value->value() : '';
    $record = array(
      'title' => $node->title,
      'body' => $body,
    );

    // Insert record to DB.
    drupal_write_record('exported_articles', $record);

    $count_exported++;
    $last_exported = $node->nid;

    if (round(memory_get_usage() / 1048576) >= $memory_limit) {
      // Print messages about the successful exports and about the memory limit.
      export_data_to_sql_print_messages(TRUE);
      exit;
    }
  }

  // Print message about the successful exports.
  export_data_to_sql_print_messages();
}

/**
 * Prints a message about successful exports.
 *
 * @param $is_memory_error
 *    If set to TRUE, an error message about memory limit will also be
 *    displayed. Defaults to FALSE.
 */
function export_data_to_sql_print_messages($is_memory_error = FALSE) {
  global $nids, $last_exported, $count_exported, $total;

  // Print message about the successful exports.
  $params = array(
    '@first' => reset($nids),
    '@last' => $last_exported,
    '@count' => $count_exported,
    '@total' => $total,
  );
  drush_log(dt('Exported nodes from node ID @first to node ID @last. Export status: Exported @count out of @total nodes.', $params), 'success');

  if ($is_memory_error) {
    // Print error about memory limit.
    drush_log(dt('Stopped before out of memory. Start process from the node ID @nid', array('@nid' => $last_exported + 1)), 'error');
  }
}
