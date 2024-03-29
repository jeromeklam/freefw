UPDATE `sys_country` SET cnty_code = 'MM' where cnty_name = 'Birmanie - myanmar';
UPDATE `sys_country` SET cnty_code = 'BF' where cnty_name = 'Burkina faso';
UPDATE `sys_country` SET cnty_code = 'CG' where cnty_name = 'Congo brazzaville';
UPDATE `sys_country` SET cnty_code = 'CD' where cnty_name = 'Congo (rép. démocratique)';
UPDATE `sys_country` SET cnty_code = 'KP' where cnty_name = 'Corée du nord';
UPDATE `sys_country` SET cnty_code = 'KR' where cnty_name = 'Corée du sud';
UPDATE `sys_country` SET cnty_code = 'CI' where cnty_name = 'Cote d\'ivoire';
UPDATE `sys_country` SET cnty_code = 'GN' where cnty_name = 'Guinée';
UPDATE `sys_country` SET cnty_code = 'KY' where cnty_name = 'Iles caïmans';
UPDATE `sys_country` SET cnty_code = 'VI' where cnty_name = 'Iles vierges us';
UPDATE `sys_country` SET cnty_code = 'IQ' where cnty_name = 'Irak';
UPDATE `sys_country` SET cnty_code = 'IE' where cnty_name = 'Irlande';
UPDATE `sys_country` SET cnty_code = 'MS' where cnty_name = 'Monserrat';
UPDATE `sys_country` SET cnty_code = 'PW' where cnty_name = 'Palau';
UPDATE `sys_country` SET cnty_code = 'KN' where cnty_name = 'Saint kitts et nevis';
UPDATE `sys_country` SET cnty_code = 'WS' where cnty_name = 'Samoa occ.';
UPDATE `sys_country` SET cnty_code = 'WS' where cnty_name = 'Samoa occ.';
UPDATE `sys_country` SET cnty_code = 'SD' where cnty_name = 'Soudan';
UPDATE `sys_country` SET cnty_code = 'SR' where cnty_name = 'Surinam';
UPDATE `sys_country` SET cnty_code = 'SZ' where cnty_name = 'Swaziland';
UPDATE `sys_country` SET cnty_code = 'CN' where cnty_name = 'Tibet';
UPDATE `sys_country` SET cnty_code = 'TC' where cnty_name = 'Turks et caicos';
UPDATE `sys_country` SET cnty_code = 'VN' where cnty_name = 'Vietnam';
delete from sys_country where not exists (select crm_client.cnty_id from crm_client where crm_client.cnty_id = sys_country.cnty_id) and cnty_code is null;