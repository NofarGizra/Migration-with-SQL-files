# This script is used to import nodes from an SQL file to a Drupal website.
# How to use this script:
#   1. Place the whole import_data_from_sql module in sites/all/modules in your Drupal project.
#   2. Place the SQL file here.........
#   2. Using the Terminal, enter your project's www directory and run this command: bash sites/all/modules/import_data_from_sql/import.sh
#   3. The nodes are ready!

# Create the tables from the SQL file.
`drush sql-connect` < sites/all/modules/import_data_from_sql/exported-data.sql

# Import the content from the tables using Migration.
drush mi ImportArticleNodes --user=1 --verbose
