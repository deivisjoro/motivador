<?php

    require_once("../config.php");
    //require_once("lib.php");
	global $COURSE, $CFG, $USER, $DB;
    
    $id = required_param('id', PARAM_INT);   // course

    if (! $course = $DB->get_record("course", array("id"=>$id))) {
        error("Course ID is incorrect");
    }	

    require_course_login($course);
    add_to_log($course->id, "motivador", "view all", "index.php?id=$course->id", "");

    $navlinks = array();
    $navlinks[] = array('name' => 'Motivador', 'link' => '', 'type' => 'externo');
    $navigation = build_navigation($navlinks);

    print_header_simple("Motivador", "", $navigation, "", "", true, "", navmenu($course));
       
    $table->head  = array ("Usuario", "Mensajes de Felicitaci&oacute;n", "Mensajes de Recomendaci&oacute;n", "Total Mensajes");
    $table->align = array ("left", "left", "left", "left");    
	
	$usuarios = $DB->get_records_sql("SELECT * from {$CFG->prefix}user where id in (
									SELECT userid
									FROM {$CFG->prefix}role_assignments
									where roleid = 5 and contextid in (
									SELECT id FROM {$CFG->prefix}context where contextlevel = 50
									and instanceid = {$course->id}
									)
									)");
	
	$recomendaciones = array();
	$felicitaciones  = array();
	$totales  = array();
	
	foreach ($usuarios as $usuario) {
		$filas = $DB->get_records_sql("select count(*) as contador     
				from {$CFG->prefix}motivador    
				where usuario = '$usuario->id' and curso = '$id' and tipomensaje = '1'");
		
		foreach ($filas as $fila) {
		   $felicitacion = $fila->contador;
		} 
		
		$filas = $DB->get_records_sql("select count(*) as contador     
				from {$CFG->prefix}motivador    
				where usuario = '$usuario->id' and curso = '$id' and tipomensaje = '2'");
		
		foreach ($filas as $fila) {
		   $recomendacion = $fila->contador;
		}
		
		$felicitaciones[$usuario->id] = $felicitacion;
		$recomendaciones[$usuario->id] = $recomendacion;
		$totales[$usuario->id] = $felicitacion+$recomendacion;
		
	}
	
	/*$usuarios = get_records_sql("select u.*, count(m.id) as contador   
				from {$CFG->prefix}user u
				left join {$CFG->prefix}motivador m on m.usuario = u.id");*/
	
	/*echo "<pre>";
	echo print_r($CFG);
	echo "</pre>";*/
	
	/*include('../mod/motivador/lang/'.$CFG->lang."/newmodule.php");
	echo $string['msg_recommeendation_user1'];*/
	
	$felicitacion = 0;
	$recomendacion = 0;
	$total = 0;
	$contador = 0;
			
    foreach ($usuarios as $usuario) {
    	$link = "<a $class href=\"view.php?id=$usuario->id&course=$id\">".$usuario->firstname." ".$usuario->lastname."</a>";
        $table->data[] = array ($link, $felicitaciones[$usuario->id], $recomendaciones[$usuario->id], $totales[$usuario->id]);
        $felicitacion  = $felicitacion + $felicitaciones[$usuario->id];
        $recomendacion = $recomendacion + $recomendaciones[$usuario->id];
        $total         = $total + $totales[$usuario->id];
        $contador++; 
    }
    
    $f = $felicitacion/$contador;
    $r = $recomendacion/$contador;
    $t = $total/$contador;
    
    $f = number_format($f, 0);
    $r = number_format($r, 0);
    $t = number_format($t, 0);
    
    $table->data[] = array ("Valores Promedio", $f, $r, $t);

    echo "<br />";

    print_table($table);    

    print_footer($course);
?>
