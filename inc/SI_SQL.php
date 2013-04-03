<?php 
class System_Info_SQL{
	public static function slow_queries(){
		global $wpdb;
		return $wpdb->get_results('SELECT db,user_host,
											avg(query_time) query_time,
											avg(rows_sent) rows_sent,
											avg(rows_examined) rows_examined,
											count(1) as times_called,
											sql_text
									FROM mysql.slow_log 
									WHERE db = :db
									GROUP BY sql_text
									ORDER BY query_time DESC,times_called DESC
									LIMIT 200;', array('db'=>$db->database_name));
	}
	public static function check_query_log(){
		global $wpdb;
		$sql = "SELECT @@log_output as log_output,
				@@slow_query_log as slow_query_log,
				@@LOG_QUERIES_NOT_USING_INDEXES as log_not_using_indexes";
		return $wpdb->get_row($sql);
	}
	public static function enable_slow_query_log_table(){
		global $wpdb;
		$sql = "SET GLOBAL log_output = 'TABLE';
				SET GLOBAL slow_query_log = 'ON'; 
				SET GLOBAL LOG_QUERIES_NOT_USING_INDEXES = 'ON';
				";
		$wpdb->get_results($sql);
	}
	public static function disable_slow_query_log_table(){	
		global $wpdb;
		$sql = "SET GLOBAL log_output = 'FILE';
				SET GLOBAL slow_query_log = 'OFF'; 
				SET GLOBAL LOG_QUERIES_NOT_USING_INDEXES = 'OFF';
				";	
		$wpdb->get_results($sql);
	}
	
	public static function optimize_table($table){
		global $wpdb;
		$sql = $wpdb->prepare("OPTIMIZE TABLE {$table}");
		 echo "<p>{$sql}</p>";
		$result = $wpdb->get_results($sql);
		System_Info_Tools::out_table($result);
	}
	public static function get_tables(){
		global $wpdb;
		$tables = $wpdb->get_results("SHOW TABLE STATUS");
		foreach($tables as &$t){	 
			if($t->Data_length > 0){
				$t->fragmentation =  round( ($t->Data_free * 100 / $t->Data_length), 2);
			}
		}
		return $tables;
	}
	public static function list_databases(){
		global $wpdb;
		$sql = "SELECT count(*) tables,
			table_schema,concat(round(sum(table_rows)/1000000,2),'MB') rows,
			concat(round(sum(data_length)/(1024*1024*1024),2),'GB') data,
			concat(round(sum(index_length)/(1024*1024*1024),2),'GB') idx,
			concat(round(sum(data_length+index_length)/(1024*1024*1024),2),'GB') total_size,
			concat(round(sum(data_free)/(1024*1024),2),'MB') free_space,
			round(sum(index_length)/sum(data_length),2) idxfrac, engine
			FROM information_schema.TABLES
			GROUP BY table_schema
			ORDER BY sum(data_length+index_length) DESC";
		$result = $wpdb->get_results($sql);	
		System_Info_Tools::out_table($result, null, true);	
	}
	public static function convert_to_file_per_table(){	
		global $wpdb;
		return;
		$sql = "SELECT concat('ALTER TABLE ',TABLE_SCHEMA ,'.',table_name,' ENGINE=InnoDB;') 
				FROM INFORMATION_SCHEMA.tables where table_type='BASE TABLE' and engine = 'InnoDB';";
		$sql = "ALERT TABLE {$schema}.{$table} ENGINE=InnoDB;";		
		#$result = $wpdb->FetchObjects('OPTIMIZE TABLE :table', array('table'=>$tbl));
		foreach($result as $r){
			#$wpdb->query($r);
		}
	}
	public static function explain_query(){
		global $wpdb;
		$sql = "EXPLAIN ".stripslashes($_POST['sql']); 
		$results = $wpdb->get_results($sql);
		include(__DIR__.'/views/Explain_Query.phtml');
		exit;
	}
}