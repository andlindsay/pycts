<?php
/*
This file is part of PYCTS, the PY151 Credit Tracking System.

PYCTS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

PYCTS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PYCTS.  If not, see <http://www.gnu.org/licenses/>.

PYCTS and this file are Copyright 2011 by Mark Platek.
*/

/* global variables */

/*
	mysql database variables
	examine extra/create_db.sql for the schema used by this application
*/
$mysql_user = "";	// the username
$mysql_pass = "";	// user's password
$mysql_server = "sql.clarkson.edu";	// server
$mysql_db = "researchcredit_points_testing";		// name of database

/*
	set this to the Active Directory server
	set $use_ad to false to disable AD authentication (e.g. in case the AD server is not available)
		it would be *very* unwise to do this, though, since it would allow anyone to log in under any account, with no password
*/
$ad_server = "ad.clarkson.edu";
$use_ad = true;

/*
	This is just the software version reported to the user, you shouldn't need to change it.
*/
$software_version = "1.0";

/*
	This sets the password for the system root user.
	This user is not authenticated against AD, and can be used to make changes to PYCTS in the event that the AD server is unavailable.
	However, it should not be used except in emergencies!
	The root user's login name is 'root', and the password must be at least one character long.
*/
$root_pw = "";
?>
