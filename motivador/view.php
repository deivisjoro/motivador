<?php

    require_once("../config.php");
	global $COURSE, $CFG, $USER, $DB;
    
    $id     = required_param('id', PARAM_INT); 
    $courseid = required_param('course', PARAM_INT);

    if (! $course = $DB->get_record("course", array("id"=>$id))) {
        error("Course ID is incorrect");
    }	
    
    require_course_login($course);
    add_to_log($course->id, "motivador", "view", "index.php?id=$id&course=$courseid", "");
    
    $estudiante = get_record('user', "id", $id);
    
    $roles =  $DB->get_records_sql("select r.roleid 
        		                from {$CFG->prefix}role_assignments r, {$CFG->prefix}context c 
        						where c.contextlevel = '50' and c.id = r.contextid and userid =".$USER->id);
    $rol = 0;
    foreach($roles as $r){
    	$rol = $r->roleid;
    }
    
    $enlace = "";
    if($rol!=5){
    	$enlace = 'index.php?id='.$courseid;
    }
    
    $navlinks = array();
    $navlinks[] = array('name' => 'Motivador', 'link' => $enlace, 'type' => 'externo');
    $navlinks[] = array('name' => $estudiante->firstname.' '.$estudiante->lastname, 'link' => '', 'type' => 'externo');
    $navigation = build_navigation($navlinks);

    print_header_simple("Motivador", "", $navigation, "", "", true, "", navmenu($course));
    
    echo "<div><h1><center>Historial de Mensajes</center></h1></div>";
       
    $table->head  = array ("Fecha/Hora", "Actividad", "Mensaje");
    $table->align = array ("left", "left", "left");
    
    $mensajes =  $DB->get_records_sql("select * from {$CFG->prefix}motivador m 
    		                     where m.usuario='$id' and m.curso = '$courseid' order by fecha desc");
	
	/*echo "<pre>";
	echo print_r($CFG);
	echo "</pre>";*/
	
	/*include('../mod/motivador/lang/'.$CFG->lang."/newmodule.php");
	echo $string['msg_recommeendation_user1'];*/
	
	$felicitacion  = 0;
	$recomendacion = 0;
	$total         = 0;	
    foreach ($mensajes as $mensaje) {
    	
    	$color='';
    	if($mensaje->tipomensaje==2){
    		$recomendacion++;
    		$color = "#FF0000";
    	}
    	else{
    		$felicitacion++;
    		$color = "#008000";
    	}
        
        $table->data[] = array ('<font color="'.$color.'">'.$mensaje->fecha."</font>", '<font color="'.$color.'"><b>'.$mensaje->accion."</b></font>", '<font color="'.$color.'"><b>'.$mensaje->mensaje."</b></font>");
        
    }
   
    $total = $felicitacion + $recomendacion; 
    $table2->head = array ('Total Mensajes de Felicitaci&oacute;n: '.$felicitacion);
    $table2->align = array ("left");
    $table3->head = array ('Total Mensajes de Recomendaci&oacute;n: '.$recomendacion);
    $table3->align = array ("left");
    $table4->head = array ('Total Mensajes Recibidos: '.$total);
    $table4->align = array ("left");

    echo "<br />";
    
    print_table($table);
    print_table($table2);
    print_table($table3);
    print_table($table4);

    print_footer($course);
?>
