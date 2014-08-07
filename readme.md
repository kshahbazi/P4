# CSCI S-15 >> Project 3

## Live URL
<http://p4-buildings-ks.gopagoda.com/>

## Description of the project 
P4 - Final project
-----------

This application brings up a portfolio of buildings, shown on the index page.
By selecting a building a 'rent-roll' of individual units/tenants appears on the detail page. 

## Details for teaching team

DB consists of five tables:  
  1.`users`  
  2.`buildings`  
  3.`units`  
  4.`leases` and   
  5.`rents`  

Buildings have a one-to-many relationship to Units.   
Units belong to buildings and have *building_id* as FK; units must belong to a building.  
Leases have a one-to-one relationship to Units; it belongsTo Units and has many Rents. It has *unit_id* as FK.  
Rents belong to Leases and have *lease_id* as FK; rents must be associated with a lease.  

The logic is performed in the Routes.php file

Implemented authentication and validation, but would like to implement client-side error-checking.

## Outside code
Used Laravel as framework

Used class examples (foobooks) as a model.