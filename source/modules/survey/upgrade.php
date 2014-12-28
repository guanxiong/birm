<?php

if(!pdo_fieldexists('survey_rows', 'suggest')) {
	pdo_query("ALTER TABLE ".tablename('survey_rows')." ADD `suggest` VARCHAR(500) NOT NULL;");
}
