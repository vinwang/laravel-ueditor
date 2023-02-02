<?php
namespace Wanglu\Ueditor;

use Illuminate\Support\ServiceProvider;

class UeditorServiceProvider extends ServiceProvider{
	
	/**
     * 指定是否延缓提供者加载。
     *
     * @var bool
     */
    //protected $defer = true;

	/**
     * Bootstrap the application events.
     * @return void
     */
    public function boot(){
		$viewPath = realpath(__DIR__ . '/../resources/views');
		$this->loadViewsFrom($viewPath, 'ueditor');
		//视图
		$this->publishes([
			$viewPath => base_path('resources/views/vendor/ueditor')
		]);
		//公共资源
		$this->publishes([
			realpath(__DIR__ . '/../resources/public/') => public_path('vendor/ueditor')
		], 'public');
		
		\View::share('ueditorUrl', url('laravel-ueditor'));
		$router = app('router');
		//auth
		$config = config('ueditor.core.route', []);
		$config['namespace'] =  __NAMESPACE__;
		// dd($config);
		//路由
		$router->group($config, function($router){
			$router->any('laravel-ueditor', 'UeditorController@index');
		});
		
	}
	/**
     * Register the service provider.
     * @return void
     */
	public function register(){
		//配置
		$configPath = realpath(__DIR__. '/../config/ueditor.php');
		$this->mergeConfigFrom($configPath, 'ueditor');
		$this->publishes([
			$configPath => config_path('ueditor.php')
		]);
	}

	/**
     * 取得提供者所提供的服务。
     *
     * @return array
     */
    public function provides(){
        //
    }
}