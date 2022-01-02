<?php if ( ! defined( 'ABSPATH' ) ) exit; 

?>
<form action="" method="post">
<?php wp_nonce_field('mk_create_db_tbl', 'create_db_tbl'); ?>
<table>
  <tbody>
    <tr class="vmiddle">
      <td><?php _e('Table name:&nbsp;','wp-file-manager-pro');?>
        <input name="table" size="40" maxlength="80" value="" class="textfield table_name" autofocus="" required="" type="text">
      </td>
    </tr>
  </tbody>
</table>
<table id="table_columns" class="noclick">
  <tbody id="records">
    <tr>
      <th></th>
      <th><?php _e('Name','wp-file-manager-pro');?></th>
      <th><?php _e('Type','wp-file-manager-pro');?> </th>
      <th><?php _e('Length/Values','wp-file-manager-pro');?></th>
      <th><?php _e('Default','wp-file-manager-pro');?></th>
      <th><?php _e('Collation','wp-file-manager-pro');?></th>
      <th><?php _e('Attributes','wp-file-manager-pro');?></th>
      <th><?php _e('Null','wp-file-manager-pro');?></th>
      <th><?php _e('Index','wp-file-manager-pro');?></th>
      <th><?php _e('A_I','wp-file-manager-pro');?></th>
      <th><?php _e('Comments','wp-file-manager-pro');?></th>
    </tr>
    <tr class="odd clone_data">
    <td><a href="javascript:void(0)" class="removeRow"><img src="<?php echo plugins_url( '/images/delete-icon-png.png', dirname(__FILE__) ); ?>" alt="<?php _e('Remove','wp-file-manager-pro');?>" title="<?php _e('Remove','wp-file-manager-pro');?>"/></a></td>
      <td class="center">
        <input name="field_name[]" maxlength="64" class="textfield field_name" title="Column" size="10" value="" type="text">
      </td>
      <td class="center">
        <select class="column_type" name="field_type[]">
          <option title="A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295"><?php _e('INT','wp-file-manager-pro');?>
          </option>
          <option title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size"><?php _e('VARCHAR','wp-file-manager-pro');?>
          </option>
          <option title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes"><?php _e('TEXT','wp-file-manager-pro');?>
          </option>
          <option title="A date, supported range is 1000-01-01 to 9999-12-31"><?php _e('DATE','wp-file-manager-pro');?>
          </option>
          <optgroup label="Numeric">
            <option title="A 1-byte integer, signed range is -128 to 127, unsigned range is 0 to 255"><?php _e('TINYINT','wp-file-manager-pro');?>
            </option>
            <option title="A 2-byte integer, signed range is -32,768 to 32,767, unsigned range is 0 to 65,535"><?php _e('SMALLINT','wp-file-manager-pro');?>
            </option>
            <option title="A 3-byte integer, signed range is -8,388,608 to 8,388,607, unsigned range is 0 to 16,777,215"><?php _e('MEDIUMINT','wp-file-manager-pro');?>
            </option>
            <option title="A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295"><?php _e('INT','wp-file-manager-pro');?>
            </option>
            <option title="An 8-byte integer, signed range is -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807, unsigned range is 0 to 18,446,744,073,709,551,615"><?php _e('BIGINT','wp-file-manager-pro');?>
            </option>
            <option disabled="disabled">-
            </option>
            <option title="A fixed-point number (M, D) - the maximum number of digits (M) is 65 (default 10), the maximum number of decimals (D) is 30 (default 0)"><?php _e('DECIMAL','wp-file-manager-pro');?>
            </option>
            <option title="A small floating-point number, allowable values are -3.402823466E+38 to -1.175494351E-38, 0, and 1.175494351E-38 to 3.402823466E+38"><?php _e('FLOAT','wp-file-manager-pro');?>
            </option>
            <option title="A double-precision floating-point number, allowable values are -1.7976931348623157E+308 to -2.2250738585072014E-308, 0, and 2.2250738585072014E-308 to 1.7976931348623157E+308"><?php _e('DOUBLE','wp-file-manager-pro');?>
            </option>
            <option title="Synonym for DOUBLE (exception: in REAL_AS_FLOAT SQL mode it is a synonym for FLOAT)"><?php _e('REAL','wp-file-manager-pro');?>
            </option>
            <option disabled="disabled">-
            </option>
            <option title="A bit-field type (M), storing M of bits per value (default is 1, maximum is 64)"><?php _e('BIT','wp-file-manager-pro');?>
            </option>
            <option title="A synonym for TINYINT(1), a value of zero is considered false, nonzero values are considered true"><?php _e('BOOLEAN','wp-file-manager-pro');?>
            </option>
            <option title="An alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE"><?php _e('SERIAL','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="Date and time">
            <option title="A date, supported range is 1000-01-01 to 9999-12-31"><?php _e('DATE','wp-file-manager-pro');?>
            </option>
            <option title="A date and time combination, supported range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59"><?php _e('DATETIME','wp-file-manager-pro');?>
            </option>
            <option title="A timestamp, range is 1970-01-01 00:00:01 UTC to 2038-01-09 03:14:07 UTC, stored as the number of seconds since the epoch (1970-01-01 00:00:00 UTC)"><?php _e('TIMESTAMP','wp-file-manager-pro');?>
            </option>
            <option title="A time, range is -838:59:59 to 838:59:59"><?php _e('TIME','wp-file-manager-pro');?>
            </option>
            <option title="A year in four-digit (4, default) or two-digit (2) format, the allowable values are 70 (1970) to 69 (2069) or 1901 to 2155 and 0000"><?php _e('YEAR','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="String">
            <option title="A fixed-length (0-255, default 1) string that is always right-padded with spaces to the specified length when stored"><?php _e('CHAR','wp-file-manager-pro');?>
            </option>
            <option title="A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size"><?php _e('VARCHAR','wp-file-manager-pro');?>
            </option>
            <option disabled="disabled">-
            </option>
            <option title="A TEXT column with a maximum length of 255 (2^8 - 1) characters, stored with a one-byte prefix indicating the length of the value in bytes"><?php _e('TINYTEXT','wp-file-manager-pro');?>
            </option>
            <option title="A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes"><?php _e('TEXT','wp-file-manager-pro');?>
            </option>
            <option title="A TEXT column with a maximum length of 16,777,215 (2^24 - 1) characters, stored with a three-byte prefix indicating the length of the value in bytes"><?php _e('MEDIUMTEXT','wp-file-manager-pro');?>
            </option>
            <option title="A TEXT column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) characters, stored with a four-byte prefix indicating the length of the value in bytes"><?php _e('LONGTEXT','wp-file-manager-pro');?>
            </option>
            <option disabled="disabled">-
            </option>
            <option title="Similar to the CHAR type, but stores binary byte strings rather than non-binary character strings"><?php _e('BINARY','wp-file-manager-pro');?>
            </option>
            <option title="Similar to the VARCHAR type, but stores binary byte strings rather than non-binary character strings"><?php _e('VARBINARY','wp-file-manager-pro');?>
            </option>
            <option disabled="disabled">-
            </option>
            <option title="A BLOB column with a maximum length of 255 (2^8 - 1) bytes, stored with a one-byte prefix indicating the length of the value"><?php _e('TINYBLOB','wp-file-manager-pro');?>
            </option>
            <option title="A BLOB column with a maximum length of 16,777,215 (2^24 - 1) bytes, stored with a three-byte prefix indicating the length of the value"><?php _e('MEDIUMBLOB','wp-file-manager-pro');?>
            </option>
            <option title="A BLOB column with a maximum length of 65,535 (2^16 - 1) bytes, stored with a two-byte prefix indicating the length of the value"><?php _e('BLOB','wp-file-manager-pro');?>
            </option>
            <option title="A BLOB column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) bytes, stored with a four-byte prefix indicating the length of the value"><?php _e('LONGBLOB','wp-file-manager-pro');?>
            </option>
            <option disabled="disabled">-
            </option>
            <option title="An enumeration, chosen from the list of up to 65,535 values or the special '' error value"><?php _e('ENUM','wp-file-manager-pro');?>
            </option>
            <option title="A single value chosen from a set of up to 64 members"><?php _e('SET','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="Spatial">
            <option title="A type that can store a geometry of any type"><?php _e('GEOMETRY','wp-file-manager-pro');?>
            </option>
            <option title="A point in 2-dimensional space"><?php _e('POINT','wp-file-manager-pro');?>
            </option>
            <option title="A curve with linear interpolation between points"><?php _e('LINESTRING','wp-file-manager-pro');?>
            </option>
            <option title="A polygon"><?php _e('POLYGON','wp-file-manager-pro');?>
            </option>
            <option title="A collection of points"><?php _e('MULTIPOINT','wp-file-manager-pro');?>
            </option>
            <option title="A collection of curves with linear interpolation between points"><?php _e('MULTILINESTRING','wp-file-manager-pro');?>
            </option>
            <option title="A collection of polygons"><?php _e('MULTIPOLYGON','wp-file-manager-pro');?>
            </option>
            <option title="A collection of geometry objects of any type"><?php _e('GEOMETRYCOLLECTION','wp-file-manager-pro');?>
            </option>
          </optgroup>    
        </select>
      </td>
      <td class="center">
        <input id="field_0_3" name="field_length[]" size="8" value="" class="textfield" type="text">
      </td>
      <td class="center">
        <select name="field_default_type[]" class="default_type">
          <option value="NONE"><?php _e('None','wp-file-manager-pro');?>
          </option>
          <option value="USER_DEFINED"><?php _e('As defined:','wp-file-manager-pro');?>
          </option>
          <option value="NULL"><?php _e('NULL','wp-file-manager-pro');?>
          </option>
          <option value="CURRENT_TIMESTAMP"><?php _e('CURRENT_TIMESTAMP','wp-file-manager-pro');?>
          </option>
        </select>
        <br>      </td>
      <td class="center">
        <select dir="ltr" name="field_collation[]" lang="en">
          <option value="">
          </option>
          <optgroup label="armscii8" title="ARMSCII-8 Armenian">
            <option value="armscii8_bin" title="Armenian, Binary"><?php _e('armscii8_bin','wp-file-manager-pro');?>
            </option>
            <option value="armscii8_general_ci" title="Armenian, case-insensitive"><?php _e('armscii8_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="ascii" title="US ASCII">
            <option value="ascii_bin" title="West European (multilingual), Binary"><?php _e('ascii_bin','wp-file-manager-pro');?>
            </option>
            <option value="ascii_general_ci" title="West European (multilingual), case-insensitive"><?php _e('ascii_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="big5" title="Big5 Traditional Chinese">
            <option value="big5_bin" title="Traditional Chinese, Binary"><?php _e('big5_bin','wp-file-manager-pro');?>
            </option>
            <option value="big5_chinese_ci" title="Traditional Chinese, case-insensitive"><?php _e('big5_chinese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="binary" title="Binary pseudo charset">
            <option value="binary" title="Binary"><?php _e('binary','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp1250" title="Windows Central European">
            <option value="cp1250_bin" title="Central European (multilingual), Binary"><?php _e('cp1250_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp1250_croatian_ci" title="Croatian, case-insensitive"><?php _e('cp1250_croatian_ci','wp-file-manager-pro');?>
            </option>
            <option value="cp1250_czech_cs" title="Czech, case-sensitive"><?php _e('cp1250_czech_cs','wp-file-manager-pro');?>
            </option>
            <option value="cp1250_general_ci" title="Central European (multilingual), case-insensitive"><?php _e('cp1250_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="cp1250_polish_ci" title="Polish, case-insensitive"><?php _e('cp1250_polish_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp1251" title="Windows Cyrillic">
            <option value="cp1251_bin" title="Cyrillic (multilingual), Binary"><?php _e('cp1251_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp1251_bulgarian_ci" title="Bulgarian, case-insensitive"><?php _e('cp1251_bulgarian_ci','wp-file-manager-pro');?>
            </option>
            <option value="cp1251_general_ci" title="Cyrillic (multilingual), case-insensitive"><?php _e('cp1251_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="cp1251_general_cs" title="Cyrillic (multilingual), case-sensitive"><?php _e('cp1251_general_cs','wp-file-manager-pro');?>
            </option>
            <option value="cp1251_ukrainian_ci" title="Ukrainian, case-insensitive"><?php _e('cp1251_ukrainian_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp1256" title="Windows Arabic">
            <option value="cp1256_bin" title="Arabic, Binary"><?php _e('cp1256_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp1256_general_ci" title="Arabic, case-insensitive"><?php _e('cp1256_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp1257" title="Windows Baltic">
            <option value="cp1257_bin" title="Baltic (multilingual), Binary"><?php _e('cp1257_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp1257_general_ci" title="Baltic (multilingual), case-insensitive"><?php _e('cp1257_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="cp1257_lithuanian_ci" title="Lithuanian, case-insensitive"><?php _e('cp1257_lithuanian_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp850" title="DOS West European">
            <option value="cp850_bin" title="West European (multilingual), Binary"><?php _e('cp850_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp850_general_ci" title="West European (multilingual), case-insensitive"><?php _e('cp850_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp852" title="DOS Central European">
            <option value="cp852_bin" title="Central European (multilingual), Binary"><?php _e('cp852_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp852_general_ci" title="Central European (multilingual), case-insensitive"><?php _e('cp852_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp866" title="DOS Russian">
            <option value="cp866_bin" title="Russian, Binary"><?php _e('cp866_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp866_general_ci" title="Russian, case-insensitive"><?php _e('cp866_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="cp932" title="SJIS for Windows Japanese">
            <option value="cp932_bin" title="Japanese, Binary"><?php _e('cp932_bin','wp-file-manager-pro');?>
            </option>
            <option value="cp932_japanese_ci" title="Japanese, case-insensitive"><?php _e('cp932_japanese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="dec8" title="DEC West European">
            <option value="dec8_bin" title="West European (multilingual), Binary"><?php _e('dec8_bin','wp-file-manager-pro');?>
            </option>
            <option value="dec8_swedish_ci" title="Swedish, case-insensitive"><?php _e('dec8_swedish_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="eucjpms" title="UJIS for Windows Japanese">
            <option value="eucjpms_bin" title="Japanese, Binary"><?php _e('eucjpms_bin','wp-file-manager-pro');?>
            </option>
            <option value="eucjpms_japanese_ci" title="Japanese, case-insensitive"><?php _e('eucjpms_japanese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="euckr" title="EUC-KR Korean">
            <option value="euckr_bin" title="Korean, Binary"><?php _e('euckr_bin','wp-file-manager-pro');?>
            </option>
            <option value="euckr_korean_ci" title="Korean, case-insensitive"><?php _e('euckr_korean_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="gb2312" title="GB2312 Simplified Chinese">
            <option value="gb2312_bin" title="Simplified Chinese, Binary"><?php _e('gb2312_bin','wp-file-manager-pro');?>
            </option>
            <option value="gb2312_chinese_ci" title="Simplified Chinese, case-insensitive"><?php _e('gb2312_chinese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="gbk" title="GBK Simplified Chinese">
            <option value="gbk_bin" title="Simplified Chinese, Binary"><?php _e('gbk_bin','wp-file-manager-pro');?>
            </option>
            <option value="gbk_chinese_ci" title="Simplified Chinese, case-insensitive"><?php _e('gbk_chinese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="geostd8" title="GEOSTD8 Georgian">
            <option value="geostd8_bin" title="Georgian, Binary"><?php _e('geostd8_bin','wp-file-manager-pro');?>
            </option>
            <option value="geostd8_general_ci" title="Georgian, case-insensitive"><?php _e('geostd8_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="greek" title="ISO 8859-7 Greek">
            <option value="greek_bin" title="Greek, Binary"><?php _e('greek_bin','wp-file-manager-pro');?>
            </option>
            <option value="greek_general_ci" title="Greek, case-insensitive"><?php _e('greek_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="hebrew" title="ISO 8859-8 Hebrew">
            <option value="hebrew_bin" title="Hebrew, Binary"><?php _e('hebrew_bin','wp-file-manager-pro');?>
            </option>
            <option value="hebrew_general_ci" title="Hebrew, case-insensitive"><?php _e('hebrew_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="hp8" title="HP West European">
            <option value="hp8_bin" title="West European (multilingual), Binary"><?php _e('hp8_bin','wp-file-manager-pro');?>
            </option>
            <option value="hp8_english_ci" title="English, case-insensitive"><?php _e('hp8_english_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="keybcs2" title="DOS Kamenicky Czech-Slovak">
            <option value="keybcs2_bin" title="Czech-Slovak, Binary"><?php _e('keybcs2_bin','wp-file-manager-pro');?>
            </option>
            <option value="keybcs2_general_ci" title="Czech-Slovak, case-insensitive"><?php _e('keybcs2_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="koi8r" title="KOI8-R Relcom Russian">
            <option value="koi8r_bin" title="Russian, Binary"><?php _e('koi8r_bin','wp-file-manager-pro');?>
            </option>
            <option value="koi8r_general_ci" title="Russian, case-insensitive"><?php _e('koi8r_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="koi8u" title="KOI8-U Ukrainian">
            <option value="koi8u_bin" title="Ukrainian, Binary"><?php _e('koi8u_bin','wp-file-manager-pro');?>
            </option>
            <option value="koi8u_general_ci" title="Ukrainian, case-insensitive"><?php _e('koi8u_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="latin1" title="cp1252 West European">
            <option value="latin1_bin" title="West European (multilingual), Binary"><?php _e('latin1_bin','wp-file-manager-pro');?>
            </option>
            <option value="latin1_danish_ci" title="Danish, case-insensitive"><?php _e('latin1_danish_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin1_general_ci" title="West European (multilingual), case-insensitive"><?php _e('latin1_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin1_general_cs" title="West European (multilingual), case-sensitive"><?php _e('latin1_general_cs','wp-file-manager-pro');?>
            </option>
            <option value="latin1_german1_ci" title="German (dictionary), case-insensitive"><?php _e('latin1_german1_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin1_german2_ci" title="German (phone book), case-insensitive"><?php _e('latin1_german2_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin1_spanish_ci" title="Spanish, case-insensitive"><?php _e('latin1_spanish_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin1_swedish_ci" title="Swedish, case-insensitive"><?php _e('latin1_swedish_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="latin2" title="ISO 8859-2 Central European">
            <option value="latin2_bin" title="Central European (multilingual), Binary"><?php _e('latin2_bin','wp-file-manager-pro');?>
            </option>
            <option value="latin2_croatian_ci" title="Croatian, case-insensitive"><?php _e('latin2_croatian_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin2_czech_cs" title="Czech, case-sensitive"><?php _e('latin2_czech_cs','wp-file-manager-pro');?>
            </option>
            <option value="latin2_general_ci" title="Central European (multilingual), case-insensitive"><?php _e('latin2_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin2_hungarian_ci" title="Hungarian, case-insensitive"><?php _e('latin2_hungarian_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="latin5" title="ISO 8859-9 Turkish">
            <option value="latin5_bin" title="Turkish, Binary"><?php _e('latin5_bin','wp-file-manager-pro');?>
            </option>
            <option value="latin5_turkish_ci" title="Turkish, case-insensitive"><?php _e('latin5_turkish_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="latin7" title="ISO 8859-13 Baltic">
            <option value="latin7_bin" title="Baltic (multilingual), Binary"><?php _e('latin7_bin','wp-file-manager-pro');?>
            </option>
            <option value="latin7_estonian_cs" title="Estonian, case-sensitive"><?php _e('latin7_estonian_cs','wp-file-manager-pro');?>
            </option>
            <option value="latin7_general_ci" title="Baltic (multilingual), case-insensitive"><?php _e('latin7_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="latin7_general_cs" title="Baltic (multilingual), case-sensitive"><?php _e('latin7_general_cs','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="macce" title="Mac Central European">
            <option value="macce_bin" title="Central European (multilingual), Binary"><?php _e('macce_bin','wp-file-manager-pro');?>
            </option>
            <option value="macce_general_ci" title="Central European (multilingual), case-insensitive"><?php _e('macce_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="macroman" title="Mac West European">
            <option value="macroman_bin" title="West European (multilingual), Binary"><?php _e('macroman_bin','wp-file-manager-pro');?>
            </option>
            <option value="macroman_general_ci" title="West European (multilingual), case-insensitive"><?php _e('macroman_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="sjis" title="Shift-JIS Japanese">
            <option value="sjis_bin" title="Japanese, Binary"><?php _e('sjis_bin','wp-file-manager-pro');?>
            </option>
            <option value="sjis_japanese_ci" title="Japanese, case-insensitive"><?php _e('sjis_japanese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="swe7" title="7bit Swedish">
            <option value="swe7_bin" title="Swedish, Binary"><?php _e('swe7_bin','wp-file-manager-pro');?>
            </option>
            <option value="swe7_swedish_ci" title="Swedish, case-insensitive"><?php _e('swe7_swedish_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="tis620" title="TIS620 Thai">
            <option value="tis620_bin" title="Thai, Binary"><?php _e('tis620_bin','wp-file-manager-pro');?>
            </option>
            <option value="tis620_thai_ci" title="Thai, case-insensitive"><?php _e('tis620_thai_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="ucs2" title="UCS-2 Unicode">
            <option value="ucs2_bin" title="Unicode (multilingual), Binary"><?php _e('ucs2_bin','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_croatian_ci" title="Croatian, case-insensitive"><?php _e('ucs2_croatian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_czech_ci" title="Czech, case-insensitive"><?php _e('ucs2_czech_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_danish_ci" title="Danish, case-insensitive"><?php _e('ucs2_danish_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_esperanto_ci" title="Esperanto, case-insensitive"><?php _e('ucs2_esperanto_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_estonian_ci" title="Estonian, case-insensitive"><?php _e('ucs2_estonian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_general_ci" title="Unicode (multilingual), case-insensitive"><?php _e('ucs2_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_general_mysql500_ci" title="Unicode (multilingual)"><?php _e('ucs2_general_mysql500_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_german2_ci" title="German (phone book), case-insensitive"><?php _e('ucs2_german2_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_hungarian_ci" title="Hungarian, case-insensitive"><?php _e('ucs2_hungarian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_icelandic_ci" title="Icelandic, case-insensitive"><?php _e('ucs2_icelandic_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_latvian_ci" title="Latvian, case-insensitive"><?php _e('ucs2_latvian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_lithuanian_ci" title="Lithuanian, case-insensitive"><?php _e('ucs2_lithuanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_persian_ci" title="Persian, case-insensitive"><?php _e('ucs2_persian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_polish_ci" title="Polish, case-insensitive"><?php _e('ucs2_polish_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_roman_ci" title="West European, case-insensitive"><?php _e('ucs2_roman_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_romanian_ci" title="Romanian, case-insensitive"><?php _e('ucs2_romanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_sinhala_ci" title="unknown, case-insensitive"><?php _e('ucs2_sinhala_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_slovak_ci" title="Slovak, case-insensitive"><?php _e('ucs2_slovak_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_slovenian_ci" title="Slovenian, case-insensitive"><?php _e('ucs2_slovenian_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_spanish2_ci" title="Traditional Spanish, case-insensitive"><?php _e('ucs2_spanish2_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_spanish_ci" title="Spanish, case-insensitive"><?php _e('ucs2_spanish_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_swedish_ci" title="Swedish, case-insensitive"><?php _e('ucs2_swedish_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_turkish_ci" title="Turkish, case-insensitive"><?php _e('ucs2_turkish_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_unicode_520_ci" title="Unicode (multilingual)"><?php _e('ucs2_unicode_520_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_unicode_ci" title="Unicode (multilingual), case-insensitive"><?php _e('ucs2_unicode_ci','wp-file-manager-pro');?>
            </option>
            <option value="ucs2_vietnamese_ci" title="unknown, case-insensitive"><?php _e('ucs2_vietnamese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="ujis" title="EUC-JP Japanese">
            <option value="ujis_bin" title="Japanese, Binary"><?php _e('ujis_bin','wp-file-manager-pro');?>
            </option>
            <option value="ujis_japanese_ci" title="Japanese, case-insensitive"><?php _e('ujis_japanese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="utf16" title="UTF-16 Unicode">
            <option value="utf16_bin" title="unknown, Binary"><?php _e('utf16_bin','wp-file-manager-pro');?>
            </option>
            <option value="utf16_croatian_ci" title="Croatian, case-insensitive"><?php _e('utf16_croatian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_czech_ci" title="Czech, case-insensitive"><?php _e('utf16_czech_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_danish_ci" title="Danish, case-insensitive"><?php _e('utf16_danish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_esperanto_ci" title="Esperanto, case-insensitive"><?php _e('utf16_esperanto_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_estonian_ci" title="Estonian, case-insensitive"><?php _e('utf16_estonian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_general_ci" title="unknown, case-insensitive"><?php _e('utf16_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_german2_ci" title="German (phone book), case-insensitive"><?php _e('utf16_german2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_hungarian_ci" title="Hungarian, case-insensitive"><?php _e('utf16_hungarian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_icelandic_ci" title="Icelandic, case-insensitive"><?php _e('utf16_icelandic_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_latvian_ci" title="Latvian, case-insensitive"><?php _e('utf16_latvian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_lithuanian_ci" title="Lithuanian, case-insensitive"><?php _e('utf16_lithuanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_persian_ci" title="Persian, case-insensitive"><?php _e('utf16_persian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_polish_ci" title="Polish, case-insensitive"><?php _e('utf16_polish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_roman_ci" title="West European, case-insensitive"><?php _e('utf16_roman_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_romanian_ci" title="Romanian, case-insensitive"><?php _e('utf16_romanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_sinhala_ci" title="unknown, case-insensitive"><?php _e('utf16_sinhala_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_slovak_ci" title="Slovak, case-insensitive"><?php _e('utf16_slovak_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_slovenian_ci" title="Slovenian, case-insensitive"><?php _e('utf16_slovenian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_spanish2_ci" title="Traditional Spanish, case-insensitive"><?php _e('utf16_spanish2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_spanish_ci" title="Spanish, case-insensitive"><?php _e('utf16_spanish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_swedish_ci" title="Swedish, case-insensitive"><?php _e('utf16_swedish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_turkish_ci" title="Turkish, case-insensitive"><?php _e('utf16_turkish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_unicode_520_ci" title="Unicode (multilingual)"><?php _e('utf16_unicode_520_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_unicode_ci" title="Unicode (multilingual), case-insensitive"><?php _e('utf16_unicode_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf16_vietnamese_ci" title="unknown, case-insensitive"><?php _e('utf16_vietnamese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="utf16le" title="UTF-16LE Unicode">
            <option value="utf16le_bin" title="unknown, Binary"><?php _e('utf16le_bin','wp-file-manager-pro');?>
            </option>
            <option value="utf16le_general_ci" title="unknown, case-insensitive"><?php _e('utf16le_general_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="utf32" title="UTF-32 Unicode">
            <option value="utf32_bin" title="unknown, Binary"><?php _e('utf32_bin','wp-file-manager-pro');?>
            </option>
            <option value="utf32_croatian_ci" title="Croatian, case-insensitive"><?php _e('utf32_croatian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_czech_ci" title="Czech, case-insensitive"><?php _e('utf32_czech_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_danish_ci" title="Danish, case-insensitive"><?php _e('utf32_danish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_esperanto_ci" title="Esperanto, case-insensitive"><?php _e('utf32_esperanto_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_estonian_ci" title="Estonian, case-insensitive"><?php _e('utf32_estonian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_general_ci" title="unknown, case-insensitive"><?php _e('utf32_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_german2_ci" title="German (phone book), case-insensitive"><?php _e('utf32_german2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_hungarian_ci" title="Hungarian, case-insensitive"><?php _e('utf32_hungarian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_icelandic_ci" title="Icelandic, case-insensitive"><?php _e('utf32_icelandic_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_latvian_ci" title="Latvian, case-insensitive"><?php _e('utf32_latvian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_lithuanian_ci" title="Lithuanian, case-insensitive"><?php _e('utf32_lithuanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_persian_ci" title="Persian, case-insensitive"><?php _e('utf32_persian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_polish_ci" title="Polish, case-insensitive"><?php _e('utf32_polish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_roman_ci" title="West European, case-insensitive"><?php _e('utf32_roman_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_romanian_ci" title="Romanian, case-insensitive"><?php _e('utf32_romanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_sinhala_ci" title="unknown, case-insensitive"><?php _e('utf32_sinhala_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_slovak_ci" title="Slovak, case-insensitive"><?php _e('utf32_slovak_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_slovenian_ci" title="Slovenian, case-insensitive"><?php _e('utf32_slovenian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_spanish2_ci" title="Traditional Spanish, case-insensitive"><?php _e('utf32_spanish2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_spanish_ci" title="Spanish, case-insensitive"><?php _e('Name','wp-file-manager-pro');?><?php _e('utf32_spanish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_swedish_ci" title="Swedish, case-insensitive"><?php _e('utf32_swedish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_turkish_ci" title="Turkish, case-insensitive"><?php _e('utf32_turkish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_unicode_520_ci" title="Unicode (multilingual)"><?php _e('utf32_unicode_520_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_unicode_ci" title="Unicode (multilingual), case-insensitive"><?php _e('utf32_unicode_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf32_vietnamese_ci" title="unknown, case-insensitive"><?php _e('utf32_vietnamese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="utf8" title="UTF-8 Unicode">
            <option value="utf8_bin" title="Unicode (multilingual), Binary"><?php _e('utf8_bin','wp-file-manager-pro');?>
            </option>
            <option value="utf8_croatian_ci" title="Croatian, case-insensitive"><?php _e('utf8_croatian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_czech_ci" title="Czech, case-insensitive"><?php _e('utf8_czech_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_danish_ci" title="Danish, case-insensitive"><?php _e('utf8_danish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_esperanto_ci" title="Esperanto, case-insensitive"><?php _e('utf8_esperanto_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_estonian_ci" title="Estonian, case-insensitive"><?php _e('utf8_estonian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_general_ci" title="Unicode (multilingual), case-insensitive"><?php _e('utf8_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_general_mysql500_ci" title="Unicode (multilingual)"><?php _e('utf8_general_mysql500_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_german2_ci" title="German (phone book), case-insensitive"><?php _e('utf8_german2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_hungarian_ci" title="Hungarian, case-insensitive"><?php _e('utf8_hungarian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_icelandic_ci" title="Icelandic, case-insensitive"><?php _e('utf8_icelandic_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_latvian_ci" title="Latvian, case-insensitive"><?php _e('utf8_latvian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_lithuanian_ci" title="Lithuanian, case-insensitive"><?php _e('utf8_lithuanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_persian_ci" title="Persian, case-insensitive"><?php _e('utf8_persian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_polish_ci" title="Polish, case-insensitive"><?php _e('utf8_polish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_roman_ci" title="West European, case-insensitive"><?php _e('utf8_roman_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_romanian_ci" title="Romanian, case-insensitive"><?php _e('utf8_romanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_sinhala_ci" title="unknown, case-insensitive"><?php _e('utf8_sinhala_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_slovak_ci" title="Slovak, case-insensitive"><?php _e('utf8_slovak_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_slovenian_ci" title="Slovenian, case-insensitive"><?php _e('utf8_slovenian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_spanish2_ci" title="Traditional Spanish, case-insensitive"><?php _e('utf8_spanish2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_spanish_ci" title="Spanish, case-insensitive"><?php _e('utf8_spanish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_swedish_ci" title="Swedish, case-insensitive"><?php _e('utf8_swedish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_turkish_ci" title="Turkish, case-insensitive"><?php _e('utf8_turkish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_unicode_520_ci" title="Unicode (multilingual)"><?php _e('utf8_unicode_520_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_unicode_ci" title="Unicode (multilingual), case-insensitive"><?php _e('utf8_unicode_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8_vietnamese_ci" title="unknown, case-insensitive"><?php _e('utf8_vietnamese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
          <optgroup label="utf8mb4" title="UTF-8 Unicode">
            <option value="utf8mb4_bin" title="Unicode (multilingual), Binary"><?php _e('utf8mb4_bin','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_croatian_ci" title="Croatian, case-insensitive"><?php _e('utf8mb4_croatian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_czech_ci" title="Czech, case-insensitive"><?php _e('utf8mb4_czech_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_danish_ci" title="Danish, case-insensitive"><?php _e('utf8mb4_danish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_esperanto_ci" title="Esperanto, case-insensitive"><?php _e('utf8mb4_esperanto_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_estonian_ci" title="Estonian, case-insensitive"><?php _e('utf8mb4_estonian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_general_ci" title="Unicode (multilingual), case-insensitive"><?php _e('utf8mb4_general_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_german2_ci" title="German (phone book), case-insensitive"><?php _e('utf8mb4_german2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_hungarian_ci" title="Hungarian, case-insensitive"><?php _e('utf8mb4_hungarian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_icelandic_ci" title="Icelandic, case-insensitive"><?php _e('utf8mb4_icelandic_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_latvian_ci" title="Latvian, case-insensitive"><?php _e('utf8mb4_latvian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_lithuanian_ci" title="Lithuanian, case-insensitive"><?php _e('utf8mb4_lithuanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_persian_ci" title="Persian, case-insensitive"><?php _e('utf8mb4_persian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_polish_ci" title="Polish, case-insensitive"><?php _e('utf8mb4_polish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_roman_ci" title="West European, case-insensitive"><?php _e('utf8mb4_roman_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_romanian_ci" title="Romanian, case-insensitive"><?php _e('utf8mb4_romanian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_sinhala_ci" title="unknown, case-insensitive"><?php _e('utf8mb4_sinhala_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_slovak_ci" title="Slovak, case-insensitive"><?php _e('utf8mb4_slovak_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_slovenian_ci" title="Slovenian, case-insensitive"><?php _e('utf8mb4_slovenian_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_spanish2_ci" title="Traditional Spanish, case-insensitive"><?php _e('utf8mb4_spanish2_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_spanish_ci" title="Spanish, case-insensitive"><?php _e('utf8mb4_spanish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_swedish_ci" title="Swedish, case-insensitive"><?php _e('utf8mb4_swedish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_turkish_ci" title="Turkish, case-insensitive"><?php _e('utf8mb4_turkish_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_unicode_520_ci" title="Unicode (multilingual)"><?php _e('utf8mb4_unicode_520_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_unicode_ci" title="Unicode (multilingual), case-insensitive"><?php _e('utf8mb4_unicode_ci','wp-file-manager-pro');?>
            </option>
            <option value="utf8mb4_vietnamese_ci" title="unknown, case-insensitive"><?php _e('utf8mb4_vietnamese_ci','wp-file-manager-pro');?>
            </option>
          </optgroup>
        </select>
      </td>
      <td class="center">
        <select name="field_attribute[]">                
          <option value="" selected="selected">
          </option>                
          <option value="BINARY"><?php _e('BINARY','wp-file-manager-pro');?>
          </option>                
          <option value="UNSIGNED"><?php _e('UNSIGNED','wp-file-manager-pro');?>
          </option>                
          <option value="UNSIGNED ZEROFILL"><?php _e('UNSIGNED ZEROFILL','wp-file-manager-pro');?>
          </option>                
          <option value="on update CURRENT_TIMESTAMP"><?php _e('on update CURRENT_TIMESTAMP','wp-file-manager-pro');?>
          </option>
        </select>
      </td>
      <td class="center">
        <input name="field_null[]" value="NULL" class="allow_null" type="checkbox">
      </td>
      <td class="center">
        <select name="field_key[]">
          <option value="none_0">---
          </option>
          <option value="primary_0" title="Primary"><?php _e('PRIMARY','wp-file-manager-pro');?>
          </option>
          <option value="unique_0" title="Unique"><?php _e('UNIQUE','wp-file-manager-pro');?>
          </option>
          <option value="index_0" title="Index"><?php _e('INDEX','wp-file-manager-pro');?>
          </option>
          <option value="fulltext_0" title="Fulltext"><?php _e('FULLTEXT','wp-file-manager-pro');?>
          </option>
        </select>
      </td>
      <td class="center">
        <input name="field_extra[]" value="AUTO_INCREMENT" type="checkbox">
      </td>
      <td class="center">
        <input name="field_comments[]" size="12" value="" class="textfield" type="text">
      </td>
    </tr>
  </tbody>
</table>
<p><a href="javascript:void(0)" id="add_new_row" class="button"><?php _e('Add Row','wp-file-manager-pro');?></a></p>
<p><input type="submit" value="<?php _e('Save','wp-file-manager-pro');?>" name="CreateTbl" class="button-primary save_tbl" /></p>
</form>

