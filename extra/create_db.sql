-- This file is part of PYCTS, the PY151 Credit Tracking System.

-- PYCTS is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

-- PYCTS is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.

-- You should have received a copy of the GNU General Public License
-- along with PYCTS.  If not, see <http://www.gnu.org/licenses/>.

-- PYCTS and this file are Copyright 2011 by Mark Platek.


create database researchcredit_points2;

use researchcredit_points2;

create table students(
s_ad		varchar(16) 	primary key,
s_lname		varchar(64)	not null,
s_fname		varchar(64)	not null,
s_prof		varchar(64)	not null);

create table users(
u_ad		varchar(16)	primary key,
u_role		int		not null,
u_lname		varchar(64)	not null,
u_fname		varchar(64)	not null);

create table studies(
st_id		int		auto_increment primary key,
st_irb		varchar(16)	not null,
st_desc		varchar(128)	not null,
st_credits	int		not null,
st_flyer	varchar(128)	not null,
st_visible	int		not null);

create table points(
p_id		mediumint	auto_increment primary key,
s_ad		varchar(16)	not null,
st_id		int		not null,
u_add_ad	varchar(16)	not null,
u_rem_ad	varchar(16),
time_add	int 		unsigned not null,
time_rem	int		unsigned,
desc_add	varchar(128),
desc_rem	varchar(128),
foreign key (s_ad) references students(s_ad),
foreign key (st_id) references studies(st_id),
foreign key (u_add_ad) references users(u_ad),
foreign key (u_rem_ad) references users(u_ad) );

