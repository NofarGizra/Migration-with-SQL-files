<?php

// This limit is used to prevent loading too many nodes at once.
$nodes_per_round = 100;

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
    ->range($i * $nodes_per_round, $nodes_per_round)
    ->execute();

  if (empty($result['node'])) {
    // Done running through all nodes, abort script.
    break;
  }

  // Load the nodes.
  $nodes = node_load_multiple(array_keys($result['node']));

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
  }

  // Write message in Terminal.
  print_r('Exported ' . $i * 100 . '-' . ($i * 100 + count($result['node']) - 1) . "\n");
}
