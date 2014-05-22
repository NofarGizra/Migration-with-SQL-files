# This script is used to export nodes from a Drupal website to an SQL file.
# How to use this script:
#   1. Place the whole export_data_to_sql module in sites/all/modules in your Drupal project.
#   2. Using the Terminal, enter your project's www directory and run this command: bash sites/all/modules/export_data_to_sql/export.sh
#   3. The SQL file is ready! It is located inside the export_data_to_sql module directory and it is named exported-data.sql .

# Export nodes to DB tables.
drush scr sites/all/modules/export_data_to_sql/export.php

# Export DB tables to SQL file.
drush sql-dump --tables-list=exported_articles,exported_news > sites/all/modules/export_data_to_sql/exported-data.sql
