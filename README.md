Migration-with-SQL-files
========================

Sometimes there is a need to import content from SQL files, for example when all the contents already exist in an old website.

Recently I came across a case like that, and I would like to share the solution with you - how to import content from an SQL file to Drupal.
In addition, since in my case the old website was Drupal-based too, I will also describe how to export content from Drupal to an SQL file, which you can later use to import the content to a new Drupal website.

So let's begin by creating two modules - one for data export, which will be installed on the old Drupal website, and the other one for data import, which will be installed on the new website.

##Module: export_data_to_sql

First let's describe what we want to accomplish, in technical terms.
**Step 1:** Creating custom tables in Drupal's database. We will create one table for each bundle we would like to export. Each of these tables will have one column for each field we would like to export from this bundle.
**Step 2:** Retrieving all needed content from the project and storing it in the custom tables we created.
**Step 3:** Creating a "dump" of the custom tables, i.e exporting our tables to an SQL file.

The full module can be found here. I will now go through important parts of the module to clarify them.

File: export_data_to_sql.install
This file only includes ``hook_schema()``, which creates custom tables (Stage 1). In our case, article is the only bundle we would like to export, so we only have one table and it is named exported_articles. The table includes 2 fields for data - title and body - which are the fields we would like to export. In addition to these data fields, we also include the id column and make it "serial". Note that the id column is mandatory for the import to work later and that this field is not necessarily equal to the NID of the article.

###File: export.php
In this file there is PHP code which gets the content we want and stores it in the custom tables we created (Stage 2).
**Lines 13-18** use EntityFieldQuery() to retrieve nodes of type article. To prevent query overload, we retrieve a maximum of 100 nodes in each query.
**Lines 28-40** go through all the nodes we retrieved and for each node they create a record with the data we would like to export and then insert the record into our custom table.

###File: export.sh
This file can be run in the Terminal using the bash command. What this file does is run the export.php file so that the needed data is exported into our custom DB tables, and then it exports the custom tables into an SQL file. So actually, it taken care of stages 2 and 3 (Stage 1 is taken care by Drupal's hook system).
Line 8 runs the code that exists in export.php.
Line 11 creates a "dump" of the selected tables into an SQL file. Note that each custom table name must be specified in this line in order for the SQL file to include it. Here we only have the table exported_articles, but know that if you want to export more than one table they must be separated with a comma and without any spaces between them, for example exported_articles,exported_news.
After running export.sh, a file named exported_data.sql will be created inside the module's directory. This file will include the custom tables with the exported data.

##Module: import_data_from_sql

Our import module will be based on the Migrate module. Here at Gizra we use this module a lot, mostly for importing content from CSV files which are filled in by the customer. In this case we will use another ability of the Migrate module - migrate content from an SQL file - the file that was previously created by our export_data_to_sql module.

###Preparation:
After running export.sh in the old Drupal project, we must copy the generated SQL file into the directory of the import_data_from_sql module in the new Drupal project. We will now go through important parts of the import_data_from_sql module and see how to use Migrate and point the source of the migration to our SQL file. The full module can be found here.

###File: migrate.entity.inc
The class ExportedDataMigration extends the Migration class and makes it suitable for our needs.
**Lines 12-21** are for tracking relationships between source rows. Here we also set the default unique identifier field name from our SQL file - in our schema it is 'id'. Usually there is no need to modify these lines, even when using CSV migration, except for changing the name of the id field in line 12 if your table uses some other name instead of 'id'.
**Lines 25-26** specify the source for the migration - for example, CSV file or SQL file. In our case we use the class MigrateSourceSQL which suits our needs, and we construct it using a basic query with the relevant table name and fields. Whenever you need to migrate from an SQL file, these lines should do the trick and there is usually no need to modify them.
**Line 29** specifies which kind of data are we importing so that Migrate knows where to put it. There is a separate class for each entity type, so if you are migrating more than one type you should put a condition here to use the correct class according to $this->entity_type. In this case we only migrate nodes, so we are using the class MigrateDestinationNode.
**Lines 32-34** make 'admin' (user 1) the author of the entity by default.

###File: article.inc
This class extends the previous class ExportedDataMigration. It is a best-practice to create a separate class for each bundle you wish to import, so it is easier to perform specific actions relevant only to the specific bundle and set the different variables to match your needs. The class ImportArticleNodes, as its name implies, is for nodes of type article.

**Lines 4-5** specify what is to be created - in this case a node of type article.
**Line 6** specifies the name of the SQL table from which to import the data for this bundle.
**Lines 7-11** specify the names of the columns in our SQL table.
**Lines 17-18** are "mapping" the 'title' property, and lines 21-23 are "mapping" the field 'body'. When using the method addFieldMapping(), the first parameter is the name of the field/property in Drupal, and the second parameter is the name of the field in the source file (the SQL file). This way we let Drupal know what to do with each field mentioned in lines 7-11, except for the 'id' field which is already handled by the class ExportedDataMigration in lines 12-21. Note that the use of the method addFieldMapping() is the same for properties and fields. In line 23 we see the method arguments(), which we always use when mapping a field of type long text in order to set the field's format.

###File: import_data_from_sql.module
Here we see the hook ``hook_migrate_api()``, which declares all the Migration classes we wish to activate i.e one class for each bundle. In our case we only have one class, ImportArticleNodes, and it is listed here.


