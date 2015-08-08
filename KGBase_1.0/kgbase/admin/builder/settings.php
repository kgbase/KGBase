<?php
//define path to sources&uploading
$src_path=array('coll'=>'src/collection/',
                'geo'=>'src/geodata/',
                'tax'=>'src/taxonomy/',
                'map'=>'src/maps/',
                'collections'=>'src/collections.xml');
$upl_path='bases';
//define properties & sources of the bases
$set=array();
//
$set[0]=array();
 $set[0]['name']='AculeataBBZ';
 $set[0]['title']='Перепончатокрылые (Hymenoptera: Aculeata) Богдинско-Баскунчакского заповедника';
 $set[0]['description']='Жалоносные перепончатокрылые Богдинско-Баскунчакского заповедника (Астраханская обл.) - по материалам сборов К.А. Гребенникова, кроме муравьев (данные приведены в базе муравьев Нижнего Поволжья).';
 $set[0]['collection']='acul_collection.xml';
 $set[0]['geodata']='acul_geodata.xml';
 $set[0]['taxonomy']='acul_taxonomy.xml';
 $set[0]['map']='bbz';
 $set[0]['src']=array();
  $set[0]['src']['tax']=array('type'=>'ODS',
                               'file'=>'Hymenoptera BBZ tax.ods');
  $set[0]['src']['data']=array();
  $set[0]['src']['data'][0]=array();
   $set[0]['src']['data'][0]['geo']=array('type'=>'ODS',
                                          'file'=>'BBZ_INSECTA_2013_2014_geodata.ods');
   $set[0]['src']['data'][0]['coll']=array('type'=>'ODBC',
                                           'src'=>'bbz_coll',
                                           'user'=>'kgbase',
                                           'pass'=>'qwerty');
//
$set[1]=array();
 $set[1]['name']='FormicidaeLV';
 $set[1]['title']='Муравьи (Hymenoptera: Formicidae) Нижнего Поволжья';
 $set[1]['description']='Муравьи (Hymenoptera: Formicidae) Нижнего Поволжья - перечень составлен по литературным источникам и материалам К.А. Гребенникова и соавторов (Дубовиков Д.А., Савранская Ж.В.), коллекционный материал приведен по материалам сборов К.А. Гребенникова.';
 $set[1]['collection']='Formicidae of LV collection.xml';
 $set[1]['geodata']='Formicidae of LV geodata.xml';
 $set[1]['taxonomy']='Formicidae of LV taxonomy.xml';
 $set[1]['map']='lv';
 $set[1]['src']=array();
  $set[1]['src']['tax']=array('type'=>'ODS',
                              'file'=>'Formicidae of LV tax.ods');
  $set[1]['src']['data']=array();
  $set[1]['src']['data'][0]=array();
   $set[1]['src']['data'][0]['geo']=array('type'=>'ODS',
                                          'file'=>'BBZ_INSECTA_2013_2014_geodata.ods');
   $set[1]['src']['data'][0]['coll']=array('type'=>'ODBC',
                                           'src'=>'bbz_coll',
                                           'user'=>'kgbase',
                                           'pass'=>'qwerty');
  $set[1]['src']['data'][1]=array();
   $set[1]['src']['data'][1]['geo']=array('type'=>'ODS',
                                          'file'=>'KGC geodata.ods');
   $set[1]['src']['data'][1]['coll']=array('type'=>'ODBC',
                                           'src'=>'kgc_coll',
                                           'user'=>'kgbase',
                                           'pass'=>'qwerty');
//
$set[2]=array();
 $set[2]['name']='HeteropteraLV';
 $set[2]['title']='Настоящие полужесткокрылые (Hemiptera: Heteroptera) Нижнего Поволжья';
 $set[2]['description']='Настоящие полужесткокрылые (Hemiptera: Heteroptera) Нижнего Поволжья - по материалам сборов К.А. Гребенникова.';
 $set[2]['collection']='HeteropteraLV collection.xml';
 $set[2]['geodata']='HeteropteraLV geodata.xml';
 $set[2]['taxonomy']='HeteropteraLV taxonomy.xml';
 $set[2]['map']='lv';
 $set[2]['src']=array();
  $set[2]['src']['tax']=array('type'=>'ODS',
                              'file'=>'Heteroptera of LV tax.ods');
  $set[2]['src']['data']=array();
  $set[2]['src']['data'][0]=array();
   $set[2]['src']['data'][0]['geo']=array('type'=>'ODS',
                                          'file'=>'BBZ_INSECTA_2013_2014_geodata.ods');
   $set[2]['src']['data'][0]['coll']=array('type'=>'ODBC',
                                           'src'=>'bbz_coll',
                                           'user'=>'kgbase',
                                           'pass'=>'qwerty');
  $set[2]['src']['data'][1]=array();
   $set[2]['src']['data'][1]['geo']=array('type'=>'ODS',
                                          'file'=>'KGC geodata.ods');
   $set[2]['src']['data'][1]['coll']=array('type'=>'ODBC',
                                           'src'=>'kgc_coll',
                                           'user'=>'kgbase',
                                           'pass'=>'qwerty');
//
$set[3]=array();
 $set[3]['name']='StaphylinidaeLV';
 $set[3]['title']='Стафилиниды (Coleoptera: Staphylinidae) Нижнего Поволжья';
 $set[3]['description']='Стафилиниды (Coleoptera: Staphylinidae) Нижнего Поволжья - перечень и коллекционный материал приведены литературным источникам, материалам сборов К.А. Гребенникова, а также любезно предоставленного для обработки коллекционного материала коллег (авторство указанано в описании колеекционных образцов).';
 $set[3]['collection']='Staphylinidae of LV collection.xml';
 $set[3]['geodata']='Staphylinidae of LV geodata.xml';
 $set[3]['taxonomy']='Staphylinidae of LV taxonomy.xml';
 $set[3]['map']='lv';
 $set[3]['src']=array();
  $set[3]['src']['tax']=array('type'=>'ODS',
                              'file'=>'Staphylinidae of LV tax.ods');
  $set[3]['src']['data']=array();
  $set[3]['src']['data'][0]=array();
   $set[3]['src']['data'][0]['geo']=array('type'=>'ODS',
                                          'file'=>'BBZ_INSECTA_2013_2014_geodata.ods');
   $set[3]['src']['data'][0]['coll']=array('type'=>'ODBC',
                                           'src'=>'bbz_coll',
                                           'user'=>'kgbase',
                                           'pass'=>'qwerty');
  $set[3]['src']['data'][1]=array();
   $set[3]['src']['data'][1]['geo']=array('type'=>'ODS',
                                          'file'=>'KGC geodata.ods');
   $set[3]['src']['data'][1]['coll']=array('type'=>'ODBC',
                                           'src'=>'kgc_coll',
                                           'user'=>'kgbase',
                                           'pass'=>'qwerty');
?>