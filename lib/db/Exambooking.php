<?php
/**
 * Table Definition for exambooking
 */

class db_Exambooking extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'exambooking';                     // table name
    public $ID;                              // int(11)  not_null primary_key auto_increment
    public $AHVNumber;                       // string(768)  not_null
    public $ExternalReference;               // string(768)  
    public $ContactTitle1;                   // string(768)  not_null
    public $ContactFirstName;                // string(768)  not_null
    public $ContactLastName;                 // string(768)  not_null
    public $Address;                         // string(768)  not_null
    public $ZIP;                             // string(768)  not_null
    public $Town;                            // string(768)  not_null
    public $Language;                        // string(768)  not_null
    public $Phone;                           // string(60)  not_null
    public $Mobile;                          // string(60)  
    public $Email;                           // string(768)  not_null
    public $DateOfBirth;                     // string(768)  
    public $OriginPlace;                     // string(768)  not_null
    public $CountryOfOrigin;                 // string(768)  not_null
    public $CompanyName;                     // string(768)  
    public $CompanyAddress;                  // string(768)  
    public $CompanyZIP;                      // string(768)  
    public $CompanyTown;                     // string(768)  
    public $MilitaryExamName;                // string(768)  not_null
    public $MilitaryExamShortcut;            // string(768)  not_null
    public $ExamStatus;                      // string(768)  not_null
    public $SerieDesignation;                // string(768)  not_null
    public $ExamDate;                        // string(768)  not_null
    public $SchoolName;                      // string(768)  not_null
    public $SchoolTown;                      // string(768)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Exambooking',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
