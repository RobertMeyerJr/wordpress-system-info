<?php 


class CommentManager{
	

	protected function comment_stats(){
		/*	
		#Comments By Type
		select comment_type, count(1) 
		FROM wp_comments 
		GROUP BY comment_type
		
		#Comments by Article
		SELECT p.post_title, count(1) as total
		FROM wp_comments c
		JOIN wp_posts p ON p.id =  c.comment_post_ID
		GROUP BY c.comment_post_ID
		order by total desc
		
		#By Agent
		SELECT c.comment_agent, COUNT(1) as total
		FROM wp_comments c
		GROUP BY c.comment_agent
		order by total desc
		
		#By Author IP
		SELECT c.comment_author_IP, COUNT(1) as total
		FROM wp_comments c
		GROUP BY c.comment_author_IP
		order by total desc
		
		*/
	}
}