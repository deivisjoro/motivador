<?php

require_once($CFG->dirroot.'/course/lib.php');

class block_motivador extends block_base {
    function init() {
        //$this->title = get_string('pluginname', 'block_motivador');
		 $this->title = "Informaci&oacute;n Retro. <img src='../motivador/AbrInfMotBoost.gif' />";

    }

    function get_content() {
		global $COURSE, $CFG, $USER, $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $roles = $DB->get_records_sql("select r.roleid 
        		                from {$CFG->prefix}role_assignments r, {$CFG->prefix}context c 
        						where c.contextlevel = '50' and c.id = r.contextid and userid =".$USER->id);
        $rol = 0;
        foreach($roles as $r){
        	$rol = $r->roleid;
        }
        
        if($rol == 5){
        	$mensajes = $DB->get_records_sql("select * from {$CFG->prefix}motivador m 
    		                     where m.usuario= ".$USER->id." and m.curso = ".$COURSE->id." order by fecha asc");
    		
    		$ultimo = "";
    		foreach ($mensajes as $mensaje) {
    			$ultimo = $mensaje;
    		}
    		
    		if($ultimo){
    			
    			$color='';
    			$tipo='';
		    	
		    	if($ultimo->tipomensaje==2){
		    		$color = "#FF0000";
		    		$tipo = 'Recomendaci&oacute;n: ';
		    	}
		    	else{
		    		$color = "#339900";
		    		$tipo = "Felicitaciones: ";
		    	}
		    	
		    	
		    	
		    	$this->content->text .= '<ul><li><b>'.$tipo.'<font color="'.$color.'">'.$ultimo->mensaje.'</font></b></li></ul>';
		    	
    			$this->content->footer .= '<a href="'.$CFG->wwwroot.'/motivador/view.php?id='.$USER->id.'&course='.$COURSE->id.'">Historial de Mensajes</a>';
    		}
    		else{
    			$this->content->text .= 'No hay mensajes';
    		}
    		                     
        	
        }
        else{
        	$this->content->text .= '<a href="'.$CFG->wwwroot.'/motivador/index.php?id='.$COURSE->id.'">Motivador</a>';
        }  	
	    return $this->content;


    }

    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }
}

