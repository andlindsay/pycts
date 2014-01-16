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


/* sets all checkboxes in the roster to the state passed */
function checkall(state) {
for(c=3; c < document.forms['roster_select'].length; c++)
	document.forms['roster_select'].elements[c].checked = state;
}

/* puts the string "Search" in the search box */
function prefill() {
	if( document.getElementById( "searchbox" ).value == "" )
		document.getElementById( "searchbox" ).value = "Search";
}

/* removes the "Search" string from the search box */
function clearfill() {
	if( document.getElementById( "searchbox" ).value == "Search" )
		document.getElementById( "searchbox" ).value = "";
}

window.onload = prefill;
