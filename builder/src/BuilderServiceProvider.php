<?php

namespace System\Builder;


use Dcat\Admin\Extend\ServiceProvider;
use System\Builder\Console\ExtensionMakeCommand;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
class BuilderServiceProvider extends ServiceProvider
{
    /**
     * 安装模块时自动创建菜单
     */
    protected $menu = [
        ['title' => '应用创建', 'uri' => 'builder/add', 'icon' => 'feather icon-codepen'],
    ];


    public function init()
    {
        parent::init();
        $this->commands(ExtensionMakeCommand::class);
    }

    public function settingForm()
    {
        return new Setting($this);
    }

}
