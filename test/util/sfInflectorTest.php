<?php

class sfInflectorTest extends UnitTestCase
{
  private static $CamelToUnderscore = array(
    "Product"               => "product",
    "SpecialGuest"          => "special_guest",
    "ApplicationController" => "application_controller"
  );

  private static $CamelWithModuleToUnderscoreWithSlash = array(
    "Admin::Product" => "admin/product",
    "Users::Commission::Department" => "users/commission/department",
    "UsersSection::CommissionDepartment" => "users_section/commission_department",
  );

  private static $ClassNameToForeignKeyWithUnderscore = array(
    "Person" => "person_id",
    "MyApplication::Billing::Account" => "account_id"
  );

  private static $ClassNameToForeignKeyWithoutUnderscore = array(
    "Person" => "personid",
    "MyApplication::Billing::Account" => "accountid"
  );
  
  private static $ClassNameToTableName = array(
    "PrimarySpokesman" => "primary_spokesman",
    "NodeChild"        => "node_child"
  );
  
  private static $UnderscoreToHuman = array(
    "employee_salary" => "Employee salary",
    "underground"     => "Underground"
  );

  public function test_camelize()
  {
    foreach (sfInflectorTest::$CamelToUnderscore as $camel => $underscore)
      $this->assertEqual($camel, sfInflector::camelize($underscore));
  }

  public function test_underscore()
  {
    foreach (sfInflectorTest::$CamelToUnderscore as $camel => $underscore)
      $this->assertEqual($underscore, sfInflector::underscore($camel));

    $this->assertEqual("html_tidy", sfInflector::underscore("HTMLTidy"));
    $this->assertEqual("html_tidy_generator", sfInflector::underscore("HTMLTidyGenerator"));
    $this->assertEqual("phone2_ext", sfInflector::underscore("Phone2Ext"));
  }

  public function test_camelize_with_module()
  {
    foreach (sfInflectorTest::$CamelWithModuleToUnderscoreWithSlash as $camel => $underscore)
      $this->assertEqual($camel, sfInflector::camelize($underscore));
  }
  
  public function test_underscore_with_slashes()
  {
    foreach (sfInflectorTest::$CamelWithModuleToUnderscoreWithSlash as $camel => $underscore)
      $this->assertEqual($underscore, sfInflector::underscore($camel));
  }

  public function test_demodulize()
  {
    $this->assertEqual("Account", sfInflector::demodulize("MyApplication::Billing::Account"));
  }

  public function test_foreign_key()
  {
    foreach (sfInflectorTest::$ClassNameToForeignKeyWithUnderscore as $klass => $foreign_key)
      $this->assertEqual($foreign_key, sfInflector::foreign_key($klass));

    foreach (sfInflectorTest::$ClassNameToForeignKeyWithoutUnderscore as $klass => $foreign_key)
      $this->assertEqual($foreign_key, sfInflector::foreign_key($klass, false));
  }

  public function test_tableize()
  {
    foreach (sfInflectorTest::$ClassNameToTableName as $class_name => $table_name)
      $this->assertEqual($table_name, sfInflector::tableize($class_name));
  }

  public function test_classify()
  {
    foreach (sfInflectorTest::$ClassNameToTableName as $class_name => $table_name)
      $this->assertEqual($class_name, sfInflector::classify($table_name));
  }
  
  public function test_humanize()
  {
    foreach (sfInflectorTest::$UnderscoreToHuman as $underscore => $human)
      $this->assertEqual($human, sfInflector::humanize($underscore));
  }
}
