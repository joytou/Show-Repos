# Show-Repos
A WordPress plugin that can make you to show your repo(s) on the wordpress through a simple shortcode. 
* Modular structure
* On-demand expansion
* Open source code. 
Show the code-managed repositories: GitHub, Gitee.

## How to Use
Add the shortcode to anywhere you want to display: 
`[show-repo src="{{code-managed plant}}" user="{{username that the repo want to display}}" repo="{{repository name that the repo want to display}}"/]`

## Example Code 
1. `[show-repo src="github" user="joytou" repo="WP-Bing-Background"/]`
2. `[show-repo src="gitee" user="joytouwu" repo="WP-Bing-Background"/]`

## Upgrade Notice 
The `./mod/` directory needs to be backed up before updating, it will be overwritten through the system update mechanism.

## Steps to Upgrade 
1. Back up the plugin directory, deactive the plugin, and then delete the plugin directory before upgrade to new version.
2. Upload and unzip the new version to its original location, active plugins, plugin configuration sits to automatically inherit.

## Installation 
1. Backup and delete the plugin directory and deactive the plugin if you has installed any elder version.
2. Upload the plugin files to the plugins directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress.

## Frequently Asked Questions 
### How to extend the other code-managed plant (or the likely service)? 
1. Create a php file in `./mod/` directory, and name it to the plant name (format: {{plant name}}.php).
2. Write the php file content as the following template:
```
<?php 
class SHOW_REPOS_MOD_{{PLANT_NAME}} {
 	public $api_url 		= '{{url}}'; //Plant api url that point to repo, usual it is such: https://{{url}}/{:user}/{:repo} 	
 	public $template_css 	= '{{css_file_url}}'; //Recommend to write it follow the format: css/{{plant name}}.css 	
 	public $template_js 	= '{{js_file_url}}'; //Recommend to write it follow the format: js/{{plant name}}.js 	
 	public $template_html 	= '{{html_file_url}}'; //Recommend to write it follow the format: {{plant name}}.html 	
 	public function data_format($original_data){ 		
 	$data = array(); 		
 	$data['name'] 			= $original_data['{{name}}'];  //The api struct data key which get the repo's name. 		
 	$data['description'] 	= $original_data['description'];  //The api struct data key which get the repo's description. 		
 	$data['url'] 			= $original_data['html_url'];  //The api struct data key which get the repo's url, which link to the repo. 		
 	$data['download_url'] 	= 'http://github.com/'.$original_data['full_name'].'/zipball/master';  //The api struct data key which get the repo's download url, or the url that can download the repo. 		
 	$data['owner'] 			= $original_data['owner']['login'];  //The api struct data key which get the repo owner's name. 		
 	$data['owner_url'] 		= $original_data['owner']['html_url'];  //The api struct data key which get the owner's url, which can link to the owner. 		
 	$data['subscribers'] 	= $original_data['subscribers_count'];  //The api struct data key which get the count of subscriber(s)/starer(s) about the repo. 		
 	$data['watchers'] 		= $original_data['watchers'];  //The api struct data key which get the count of watcher(s) about the repo. 		
 	$data['forks'] 			= $original_data['forks'];  //The api struct data key which get the count of has forked about the repo. 		
 	$data['home_page']		= $original_data['homepage'];  //The api struct data key which get theurl of the repo's homepage. 		
 	$data['branch']			= $original_data['default_branch'];  //The api struct data key which get the default branch name of the repo. 		
 	$data['ctime']			= $original_data['created_at'];  //The api struct data key which get the repo's created time. 		
 	$data['mtime']			= $original_data['updated_at'];  //The api struct data key which get the repo's last updated time. 		
 	$data['ptime']			= $original_data['pushed_at'];  //The api struct data key which get the repo's last pushed time. 		return $data; 	
 	} 
 }
```
 3. Add and write the html template file in the directory `./mod/template/`, which want to display in the shortcode, and add such label where want to display the specified infomation:
```
	{{name}} => Repo's name 	
	{{description}} => Repo's description 	
	{{url}} => Repo's url 	
	{{download_url}} => The url that can download the repo 	
	{{owner}} => Repo owner's name 	
	{{owner_url}} => Repo owner's url 	
	{{subscribers}} => The num of subscribing/staring the repo 	
	{{watchers}} => The num of watching the repo 	
	{{forks}} => The num of forking the repo 	
	{{home_page}} => Repo's homepage url, which can link to repo's homepage 	
	{{branch}} => Repo's default branch name 	
	{{ctime}} => Repo's created time 	
	{{mtime}} => Repo's last updated time 	
	{{ptime}} => Repo's last pushed time
```
3. Add the css/js file to the directory `./mod/template/css/` / `./mod/template/js/` as if needed, and name it(s) to the plant name (format: {{plant name}}.js / {{plant name}}.css)

### I had added the shortcode, but it still does not display or it display the error message. 
1. Please ensure there are files in directory `./mod/`.
2. Please check if you write the correct infomation, like src(point to the plant name), user(the repo's owner), repo(the repo).
3. If you had modified any file(s), you can modify it(s) to the correct, refer to FAQ 'How to extend the other code-managed plant (or the likely service)?'.
4. If it sitll, redownload and active the plugin.
5. If you have any doubt(s), please email me <joytou.wu@qq.com>.

## Screenshots 
- null

## Changelog 
### 1.0.0 
* The first version.

## Upgrade Notice 
- The `./mod/` directory needs to be backed up before updating, it will be overwritten through the system update mechanism.