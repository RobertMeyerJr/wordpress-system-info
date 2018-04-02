<?php 
class System_Info_SQL{

	public static function optimize_table($table){
		global $wpdb;
		$sql = "OPTIMIZE TABLE {$table}";
		$result = $wpdb->get_results($sql);
		$update_stats = "ANALYZE TABLE {$table}";
		return $result;
	}

	public static function get_tables(){
		global $wpdb;
		$sql = "SELECT * FROM information_schema.tables 
				WHERE table_schema = DATABASE() 
				ORDER BY 
				data_length desc";
		$tables = $wpdb->get_results($sql);
		foreach($tables as &$t){	 
			if($t->DATA_LENGTH > 0){
				$t->fragmentation =  round( ($t->DATA_FREE * 100 / $t->DATA_LENGTH), 2);
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
}