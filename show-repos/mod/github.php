<?php
class SHOW_REPOS_MOD_GITHUB{
	public $api_url 		= 'https://api.github.com/repos/{:user}/{:repo}';
	public $template_css 	= 'css/github.css';
	public $template_js 	= 'js/github.js';
	public $template_html 	= 'github.html';
	public function data_format($original_data){
		$data = array();
		$data['name'] 			= $original_data['name'];
		$data['description'] 	= $original_data['description'];
		$data['url'] 			= $original_data['html_url'];
		$data['download_url'] 	= 'http://github.com/'.$original_data['full_name'].'/zipball/master';
		$data['owner'] 			= $original_data['owner']['login'];
		$data['owner_url'] 		= $original_data['owner']['html_url'];
		$data['subscribers'] 	= $original_data['subscribers_count'];
		$data['watchers'] 		= $original_data['watchers'];
		$data['forks'] 			= $original_data['forks'];
		$data['home_page']		= $original_data['homepage'];
		$data['branch']			= $original_data['default_branch'];
		$data['ctime']			= $original_data['created_at'];
		$data['mtime']			= $original_data['updated_at'];
		$data['ptime']			= $original_data['pushed_at'];
		return $data;
	}
}