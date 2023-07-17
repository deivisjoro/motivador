<?php

    require_once("../config.php");
        
    $navlinks = array();
    $navlinks[] = array('name' => 'Motivador', 'link' => '', 'type' => 'externo');
    $navigation = build_navigation($navlinks);

    print_header_simple("Motivador", "", $navigation, "", "", true, "", "");
    
    $table->head  = array ("nombre", "fecha");
    $table->align = array ("left", "left");
       
    $hoy_unix = mktime();
    
    //para incluir el dia limite vamos a restar 24 horas a la fecha unix
    $hoy_unix = $hoy_unix - 86400;
    
    //obtener actividades subir archivo en fechas habiles
    $actividades = get_records_sql("select * from {$CFG->prefix}data where '$hoy_unix' < timeavailableto");
       
    echo "<br />";
    
    foreach ($actividades as $actividad) {
    	//resto a una fecha la otra
		$segundos_diferencia = $actividad->timeavailableto - $hoy_unix;
				
		//convierto segundos en días
		$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
		echo $dias_diferencia."<br />";
		
		if($dias_diferencia<5){
    	
	    	//$fecha = usergetdate($usuario->timeavailableto);
	    	//$fecha = userdate($usuario->timeavailableto);
	    	
	    	//obtener nombre del tema
			$temas = get_records_sql("SELECT s.* from mdl_course_sections s, mdl_course_modules m
									  where ".$actividad->id." = m.instance and m.section = s.id and m.module = 4");
			$tema = "";
    		foreach ($temas as $a) {
    			$tema = $a;
    		}	    	
	        
	        //obtener estudiantes
	        $estudiantes = get_records_sql("SELECT * from mdl_user where id in (
											SELECT userid
											FROM mdl_role_assignments
											where roleid = 5 and contextid in (
											SELECT id FROM mdl_context where contextlevel = 50
											and instanceid = ".$actividad->course."
											)
											)");
											
			foreach($estudiantes as $estudiante){
				//verificar si estudiante envio actividad
				$actividad_enviada = get_records_sql("select * from mdl_data_records where dataid = '".$actividad->id."' and userid = '".$estudiante->id."'"); 
				
				if(($actividad_enviada) && (count($actividad_enviada)>=$actividad->requiredentries)){
					
					//verificar si se envio mensaje
					$mensaje_enviado = get_records_sql("select * 
							                            from mdl_motivador 
							                            where actividad = ".$actividad->id."  
							                            and  usuario = ".$estudiante->id."  
							                            and tipomensaje = 1 
							                            and tipoactividad = 1");
					if(!$mensaje_enviado){
						$dia = date("Y-m-d");
					    $hora = date("H:i:s");
					    $fecha = $dia." ".$hora;
						$mensaje = new object();
						$mensaje->fecha         = time();
						$mensaje->actividad     = $actividad->id;
						$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname." Felicitaciones, has completado la tarea ".$actividad->name.", del tema ".strip_tags($tema->summary)." a tiempo.";
						$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
						$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
						$mensaje->usuario       = $estudiante->id;
			            $mensaje->tipomensaje   = 1;
			            $mensaje->curso         = $actividad->course;
			            $mensaje->tipoactividad = 1;
			            $mensaje->accion        = 'Subir documento';
	            
						insert_record("motivador", $mensaje);
						
						$encabezado = "From:root@tabor.upbmonteria.edu.co";
				    					    					
				    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
				    		echo "no se envio";
				        }
						
					}
					
				}
				else{
														
					//verificar si se envio mensaje
					if($actividad_enviada) $cantidad = count($actividad_enviada);
					else $cantidad = 0;
					
					$mensaje_enviado = get_records_sql("select * 
							                            from mdl_motivador 
							                            where actividad = ".$actividad->id."  
							                            and  usuario = ".$estudiante->id."  
							                            and tipomensaje = 2 
							                            and tipoactividad = 1 
							                            and cantidad = ".$cantidad);
					if(!$mensaje_enviado){
						$mensaje = new object();
						$dia = date("Y-m-d");
					    $hora = date("H:i:s");
					    $fecha = $dia." ".$hora;
						$mensaje->fecha         = time();;
						$mensaje->actividad     = $actividad->id;
						$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Te recomiendo completar la tarea ".$actividad->name.", del tema ".strip_tags($tema->summary)." ".($actividad->requiredentries-$cantidad)." vez/veces";
						$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
						$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
						$mensaje->usuario       = $estudiante->id;
			            $mensaje->tipomensaje   = 2;
			            $mensaje->curso         = $actividad->course;
			            $mensaje->tipoactividad = 1;
			            $mensaje->accion        = 'Subir documento';
			            $mensaje->cantidad      = $cantidad;
	            
						insert_record("motivador", $mensaje);
						
						$encabezado = "From:root@tabor.upbmonteria.edu.co";
				    					    					
				    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
				    		echo "no se envio";
				        }
					}
				}				
				
			}
	        
		}
    }

    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    
    echo "<br />";
    
    //obtener actividades comentar archivo en fechas habiles
    $actividades = get_records_sql("select * from {$CFG->prefix}data where '$hoy_unix' < timecoment");
       
    echo "<br />";
    
    foreach ($actividades as $actividad) {
    	
    	//resto a una fecha la otra
		$segundos_diferencia = $actividad->timecoment - $hoy_unix;
				
		//convierto segundos en días
		$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
		echo $dias_diferencia."<br />";
		
		if($dias_diferencia<5){
			
			//obtener nombre del tema
			$temas = get_records_sql("SELECT s.* from mdl_course_sections s, mdl_course_modules m
									  where ".$actividad->id." = m.instance and m.section = s.id and m.module = 4");
			$tema = "";
    		foreach ($temas as $a) {
    			$tema = $a;
    		}
	        
	        //obtener estudiantes
	        $estudiantes = get_records_sql("SELECT * from mdl_user where id in (
											SELECT userid
											FROM mdl_role_assignments
											where roleid = 5 and contextid in (
											SELECT id FROM mdl_context where contextlevel = 50
											and instanceid = ".$actividad->course."
											)
											)");
											
			foreach($estudiantes as $estudiante){
				
				//verificar si estudiante comento actividad
				$actividad_comentada = get_records_sql("select c.* 
						                                from mdl_data_comments c, mdl_data_records r 
						                                where c.userid = ".$estudiante->id." and c.recordid = r.id and r.dataid = ".$actividad->id);
								
				if(($actividad_comentada) && (count($actividad_comentada)>=$actividad->requiredcoment)){
					//verificar si se envio mensaje
					$mensaje_enviado = get_records_sql("select * 
							                            from mdl_motivador 
							                            where actividad = ".$actividad->id."  
							                            and  usuario = ".$estudiante->id."  
							                            and tipomensaje = 1 
							                            and tipoactividad = 2");
					//$mensaje_enviado = get_record('motivador', "actividad", $actividad->id, "usuario", $estudiante->id, "tipomensaje", 1, "tipoactividad", 2);
					if(!$mensaje_enviado){
						$dia = date("Y-m-d");
					    $hora = date("H:i:s");
					    $fecha = $dia." ".$hora;
						$mensaje = new object();
						$mensaje->fecha         = time();
						$mensaje->actividad     = $actividad->id;
						$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname." Felicitaciones, has comentado en la tarea ".$actividad->name.", del tema ".strip_tags($tema->summary)." a tiempo.";
						$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
						$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
						$mensaje->usuario       = $estudiante->id;
			            $mensaje->tipomensaje   = 1;
			            $mensaje->curso         = $actividad->course;
			            $mensaje->tipoactividad = 2;
			            $mensaje->accion        = 'Realizar comentario';
	            
						insert_record("motivador", $mensaje);
						
						$encabezado = "From:root@tabor.upbmonteria.edu.co";
				    					    					
				    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
				    		echo "no se envio";
				        }
					}
					
				}
				else{
					
					//verificar si se envio mensaje
					if($actividad_comentada) $cantidad = count($actividad_comentada);
					else $cantidad = 0;
					$mensaje_enviado = get_records_sql("select * 
							                            from mdl_motivador 
							                            where actividad = ".$actividad->id."  
							                            and  usuario = ".$estudiante->id."  
							                            and tipomensaje = 2 
							                            and tipoactividad = 2 
							                            and cantidad = ".$cantidad);
					if(!$mensaje_enviado){						
						$mensaje = new object();
						$dia = date("Y-m-d");
					    $hora = date("H:i:s");
					    $fecha = $dia." ".$hora;
						$mensaje->fecha         = time();;
						$mensaje->actividad     = $actividad->id;
						$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", te recomiendo comentar la tarea ".$actividad->name.", del tema ".strip_tags($tema->summary)." ".($actividad->requiredcoment-$cantidad)." vez/veces";
						$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
						$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
						$mensaje->usuario       = $estudiante->id;
			            $mensaje->tipomensaje   = 2;
			            $mensaje->curso         = $actividad->course;
			            $mensaje->tipoactividad = 2;
			            $mensaje->accion        = 'Realizar comentario';
			            $mensaje->cantidad      = $cantidad;
	            
						insert_record("motivador", $mensaje);
						
						$encabezado = "From:root@tabor.upbmonteria.edu.co";
				    					    					
				    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
				    		echo "no se envio";
				        }
					}
				}				
				
			}
	        
		}
    }
    
   
    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    
    echo "<br />";
    
    //obtener actividades calificar archivo en fechas habiles
    $actividades = get_records_sql("select * from {$CFG->prefix}data where '$hoy_unix' < timerating");
       
    echo "<br />";
    
    foreach ($actividades as $actividad) {
    	//resto a una fecha la otra
		$segundos_diferencia = $actividad->timerating - $hoy_unix;
				
		//convierto segundos en días
		$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
		echo $dias_diferencia."<br />";
		
		if($dias_diferencia<5){
			
			//obtener nombre del tema
			$temas = get_records_sql("SELECT s.* from mdl_course_sections s, mdl_course_modules m
									  where ".$actividad->id." = m.instance and m.section = s.id and m.module = 4");
			$tema = "";
    		foreach ($temas as $a) {
    			$tema = $a;
    		}
    			        
	        //obtener estudiantes
	        $estudiantes = get_records_sql("SELECT * from mdl_user where id in (
											SELECT userid
											FROM mdl_role_assignments
											where roleid = 5 and contextid in (
											SELECT id FROM mdl_context where contextlevel = 50
											and instanceid = ".$actividad->course."
											)
											)");
											
			foreach($estudiantes as $estudiante){
				
				//verificar si estudiante califico actividad
				$actividad_calificada = get_records_sql("select c.* 
						                                from mdl_data_ratings c, mdl_data_records r 
						                                where c.userid = ".$estudiante->id." and c.recordid = r.id and r.dataid = ".$actividad->id);
								
				if(($actividad_calificada) && (count($actividad_calificada)>=$actividad->requiredrating)){
					//verificar si se envio mensaje
					$mensaje_enviado = get_records_sql("select * 
							                            from mdl_motivador 
							                            where actividad = ".$actividad->id."  
							                            and  usuario = ".$estudiante->id."  
							                            and tipomensaje = 1 
							                            and tipoactividad = 3");
					if(!$mensaje_enviado){
						$dia = date("Y-m-d");
					    $hora = date("H:i:s");
					    $fecha = $dia." ".$hora;
						$mensaje = new object();
						$mensaje->fecha         = time();
						$mensaje->actividad     = $actividad->id;
						$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Felicitaciones has calificado en la tarea ".$actividad->name.", del tema ".strip_tags($tema->summary)." a tiempo";
						$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
						$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
						$mensaje->usuario       = $estudiante->id;
			            $mensaje->tipomensaje   = 1;
			            $mensaje->curso         = $actividad->course;
			            $mensaje->tipoactividad = 3;
			            $mensaje->accion        = 'Realizar calificaci&oacute;n';
	            
						insert_record("motivador", $mensaje);
						
						$encabezado = "From:root@tabor.upbmonteria.edu.co";
				    					    					
				    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
				    		echo "no se envio";
				        }
					}
					
				}
				else{
					
					//verificar si se envio mensaje
					if($actividad_calificada) $cantidad = count($actividad_calificada);
					else $cantidad = 0;
					$mensaje_enviado = get_records_sql("select * 
							                            from mdl_motivador 
							                            where actividad = ".$actividad->id."  
							                            and  usuario = ".$estudiante->id."  
							                            and tipomensaje = 2 
							                            and tipoactividad = 3
							                            and cantidad = ".$cantidad);
					if(!$mensaje_enviado){
						$mensaje = new object();
						$dia = date("Y-m-d");
					    $hora = date("H:i:s");
					    $fecha = $dia." ".$hora;
						$mensaje->fecha         = time();;
						$mensaje->actividad     = $actividad->id;
						$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", te recomiendo calificar la tarea ".$actividad->name.", del tema ".strip_tags($tema->summary)." ".($actividad->requiredrating-$cantidad)." vez/veces";
						$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
						$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
						$mensaje->usuario       = $estudiante->id;
			            $mensaje->tipomensaje   = 2;
			            $mensaje->curso         = $actividad->course;
			            $mensaje->tipoactividad = 3;
			            $mensaje->accion        = 'Realizar calificaci&oacute;n';
			            $mensaje->cantidad      = $cantidad;
	            
						insert_record("motivador", $mensaje);
						
						$encabezado = "From:root@tabor.upbmonteria.edu.co";
				    					    					
				    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
				    		echo "no se envio";
				        }
					}
				}				
				
			}
	        
		}
    }
    
    print_table($table);

    print_footer($course);
    
    
    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    /*********************Segundo Conjunto de Reglas ***************************/
    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    /***************************************************************************/
    
    //obtener actividades 
    $actividades = get_records_sql("select * from {$CFG->prefix}data");
    echo "<br />";
    
    foreach ($actividades as $actividad) {
    	
    	//obtener nombre del tema
		$temas = get_records_sql("SELECT s.* from mdl_course_sections s, mdl_course_modules m
								  where ".$actividad->id." = m.instance and m.section = s.id and m.module = 4");
		$tema = "";
		foreach ($temas as $a) {
			$tema = $a;
		}	    	
        
        //obtener estudiantes
        $estudiantes = get_records_sql("SELECT * from mdl_user where id in (
										SELECT userid
										FROM mdl_role_assignments
										where roleid = 5 and contextid in (
										SELECT id FROM mdl_context where contextlevel = 50
										and instanceid = ".$actividad->course."
										)
										)");
										
		foreach($estudiantes as $estudiante){
			
			$envios       = get_record('data_records', "dataid", $actividad->id, "userid", $estudiante->id);
			$comentarios  = get_records_sql("select c.* from mdl_data_comments c, mdl_data_records r where c.userid = ".$estudiante->id." and c.recordid = r.id and r.dataid = ".$actividad->id);
			$valoraciones =	get_records_sql("select c.* from mdl_data_ratings c, mdl_data_records r where c.userid = ".$estudiante->id." and c.recordid = r.id and r.dataid = ".$actividad->id);
						
			end($envios);
			$envio = current($envios);
			end($comentarios);
			$comentario = current($comentarios);
			end($valoraciones);
			$valoracion = current($valoraciones);
			
			$dateupload = $envio->timecreated;
			$datecoment = $comentario->created;
			$daterating = $valoracion->created;
			
			//1. Si ha CALIFICADO algún documento Y del tema X 
			//y no ha realizado ningún COMENTARIO en el documento Y del tema X
			if($daterating > $datecoment){
				//verificar si se envio mensaje
				$mensaje_enviado = get_records_sql("select * 
						                            from mdl_motivador 
						                            where actividad = ".$actividad->id."  
						                            and  usuario = ".$estudiante->id."  
						                            and tipomensaje = 1 
						                            and tipoactividad = 4");
				if(!$mensaje_enviado){
					$mensaje = new object();
					$dia = date("Y-m-d");
				    $hora = date("H:i:s");
				    $fecha = $dia." ".$hora;
					$mensaje->fecha         = time();;
					$mensaje->actividad     = $actividad->id;
					$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Felicitaciones has completado la tarea calificar documento del tema ".strip_tags($tema->summary)." despues de comentar documento";
					$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
					$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
					$mensaje->usuario       = $estudiante->id;
		            $mensaje->tipomensaje   = 1;
		            $mensaje->curso         = $actividad->course;
		            $mensaje->tipoactividad = 4;
		            $mensaje->accion        = 'Calificar despues de comentar';
            
					insert_record("motivador", $mensaje);
					
					$encabezado = "From:root@tabor.upbmonteria.edu.co";
			    					    					
			    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
			    		echo "no se envio";
			        }
				}
				
			}
			else{
				//verificar si se envio mensaje
				$mensaje_enviado = get_records_sql("select * 
						                            from mdl_motivador 
						                            where actividad = ".$actividad->id."  
						                            and  usuario = ".$estudiante->id."  
						                            and tipomensaje = 2 
						                            and tipoactividad = 4");
				if(!$mensaje_enviado){
					$mensaje = new object();
					$dia = date("Y-m-d");
				    $hora = date("H:i:s");
				    $fecha = $dia." ".$hora;
					$mensaje->fecha         = time();;
					$mensaje->actividad     = $actividad->id;
					$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Te recomiendo completar la tarea comentar documento del tema ".strip_tags($tema->summary)." antes de calificar documento";
					$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
					$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
					$mensaje->usuario       = $estudiante->id;
		            $mensaje->tipomensaje   = 2;
		            $mensaje->curso         = $actividad->course;
		            $mensaje->tipoactividad = 4;
		            $mensaje->accion        = 'Comentar antes de calificar';
            
					insert_record("motivador", $mensaje);
					
					$encabezado = "From:root@tabor.upbmonteria.edu.co";
			    					    					
			    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
			    		echo "no se envio";
			        }
				}
			}
			
			//2. Si ha calificado a un documento Y del tema X 
			//sin haber agregado su propio documento
			if($dateupload < $daterating){
				//verificar si se envio mensaje
				$mensaje_enviado = get_records_sql("select * 
						                            from mdl_motivador 
						                            where actividad = ".$actividad->id."  
						                            and  usuario = ".$estudiante->id."  
						                            and tipomensaje = 1 
						                            and tipoactividad = 5");
				if(!$mensaje_enviado){
					$mensaje = new object();
					$dia = date("Y-m-d");
				    $hora = date("H:i:s");
				    $fecha = $dia." ".$hora;
					$mensaje->fecha         = time();;
					$mensaje->actividad     = $actividad->id;
					$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Felicitaciones has completado la tarea calificar documento del tema ".strip_tags($tema->summary)." despues de subir documento";
					$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
					$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
					$mensaje->usuario       = $estudiante->id;
		            $mensaje->tipomensaje   = 1;
		            $mensaje->curso         = $actividad->course;
		            $mensaje->tipoactividad = 5;
		            $mensaje->accion        = 'Calificar despues de subir documento';
            
					insert_record("motivador", $mensaje);
					
					$encabezado = "From:root@tabor.upbmonteria.edu.co";
			    					    					
			    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
			    		echo "no se envio";
			        }
				}
			}
			else{
				//verificar si se envio mensaje
				$mensaje_enviado = get_records_sql("select * 
						                            from mdl_motivador 
						                            where actividad = ".$actividad->id."  
						                            and  usuario = ".$estudiante->id."  
						                            and tipomensaje = 2 
						                            and tipoactividad = 5");
				if(!$mensaje_enviado){
					$mensaje = new object();
					$dia = date("Y-m-d");
				    $hora = date("H:i:s");
				    $fecha = $dia." ".$hora;
					$mensaje->fecha         = time();;
					$mensaje->actividad     = $actividad->id;
					$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Te recomiendo completar la tarea subir documento del tema ".strip_tags($tema->summary)." antes de calificar un documento";
					$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
					$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
					$mensaje->usuario       = $estudiante->id;
		            $mensaje->tipomensaje   = 2;
		            $mensaje->curso         = $actividad->course;
		            $mensaje->tipoactividad = 5;
		            $mensaje->accion        = 'Subir documento antes de calificar';
            
					insert_record("motivador", $mensaje);
					
					$encabezado = "From:root@tabor.upbmonteria.edu.co";
			    					    					
			    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
			    		echo "no se envio";
			        }
				}
			}  
			
			//3. Si ha comentado a un documento Y del tema X 
			//sin haber agregado su propio documento  
			if($dateupload < $datecoment){
				//verificar si se envio mensaje
				$mensaje_enviado = get_records_sql("select * 
						                            from mdl_motivador 
						                            where actividad = ".$actividad->id."  
						                            and  usuario = ".$estudiante->id."  
						                            and tipomensaje = 1 
						                            and tipoactividad = 6");
				if(!$mensaje_enviado){
					$mensaje = new object();
					$dia = date("Y-m-d");
				    $hora = date("H:i:s");
				    $fecha = $dia." ".$hora;
					$mensaje->fecha         = time();;
					$mensaje->actividad     = $actividad->id;
					$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Felicitaciones has completado la tarea comentar documento del tema ".strip_tags($tema->summary)." despues de subir documento";
					$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
					$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
					$mensaje->usuario       = $estudiante->id;
		            $mensaje->tipomensaje   = 1;
		            $mensaje->curso         = $actividad->course;
		            $mensaje->tipoactividad = 6;
		            $mensaje->accion        = 'Comentar despues de subir documento';
            
					insert_record("motivador", $mensaje);
					
					$encabezado = "From:root@tabor.upbmonteria.edu.co";
			    					    					
			    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
			    		echo "no se envio";
			        }
				}
			}
			else{
				//verificar si se envio mensaje
				$mensaje_enviado = get_records_sql("select * 
						                            from mdl_motivador 
						                            where actividad = ".$actividad->id."  
						                            and  usuario = ".$estudiante->id."  
						                            and tipomensaje = 2 
						                            and tipoactividad = 6");
				if(!$mensaje_enviado){
					$mensaje = new object();
					$dia = date("Y-m-d");
				    $hora = date("H:i:s");
				    $fecha = $dia." ".$hora;
					$mensaje->fecha         = time();;
					$mensaje->actividad     = $actividad->id;
					$mensaje->mensaje       = "Estimado/a ".$estudiante->firstname.' '.$estudiante->lastname.", Te recomiendo completar la tarea subir documento del tema ".strip_tags($tema->summary)." antes de comentar un documento";
					$mensaje->mensaje      .= "<br />Saludos, Moodle Visitanos: http://tabor.upbmonteria.edu.co/moodle/course/view.php?id=".$actividad->course;
					$mensaje->mensaje      .= '<br /><font color="#000000">'.$fecha.'</font>';
					$mensaje->usuario       = $estudiante->id;
		            $mensaje->tipomensaje   = 2;
		            $mensaje->curso         = $actividad->course;
		            $mensaje->tipoactividad = 6;
		            $mensaje->accion        = 'Subir documento antes de comentar';
            
					insert_record("motivador", $mensaje);
					
					$encabezado = "From:root@tabor.upbmonteria.edu.co";
			    					    					
			    	if (mail($estudiante->email, "Moodle", $mensaje->mensaje, $encabezado) == false){
			    		echo "no se envio";
			        }
				}
			} 
			 
		}
    	
    }   
    
    
?>
