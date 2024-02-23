<?php

namespace System\Builder\Http\Controllers;


use Dcat\Admin\Form;
use Dcat\Admin\Http\Controllers\ExtensionController;
use Dcat\Admin\Http\Repositories\Extension;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Support\Helper;
use Dcat\Admin\Support\StringOutput;
use Illuminate\Support\Facades\Artisan;
use System\Builder\BuilderServiceProvider;

class BuilderController extends ExtensionController
{
    public function add(Content $content, BuilderServiceProvider $provider)
    {
        return $content->header($provider->composerProperty->alias)
            ->description($provider->composerProperty->description)
            ->body($this->form());
    }

    public function form()
    {
        return Form::make(new Extension(), function (Form $form) {
            $form->disableViewCheck();
            $form->disableListButton();
            $form->disableCreatingCheck();
            $form->disableEditingCheck();

            $form->text('name', '包名')->placeholder('例:  system/builder')->rules(function () {
                return [function ($attribute, $value, $fail) {
                    if (!Helper::validateExtensionName($value)) {
                        return $fail("$value 不是一个有效包名");
                    }
                }];
            })->width(5)->required();
            $form->text('namespace', '命名空间')->placeholder('例:  System\Builder')->width(5)->required();
            $form->text('alias', '应用名称')->placeholder('例:  系统 - 创建应用')->width(5)->required();
            $form->text('description', '应用描述')->placeholder('例:  快速生成应用骨架代码')->width(5)->required();
            $form->text('author', '开发者')->placeholder('例:  张三')->width(5)->required();
            $form->text('email', '开发者邮箱')->placeholder('例:  zhangsan@163.com')->width(5)->required();

            $self = $this;

            $form->saving(function (Form $form) use ($self) {
                $package = $form->name;
                $namespace = $form->namespace;
                $alias = $form->alias;
                $description = $form->description;
                $author = $form->author;
                $email = $form->email;

                if ($package) {
                    $results = $self->buildExtension($package, $namespace, 1, $alias, $description, $author, $email);
                    return $form->response()->redirect(url('admin/auth/extensions'))->timeout(10)->success($results);
                }
            });
        });
    }

    public function buildExtension($package, $namespace, $type, $alias, $description, $author, $email)
    {
        $namespace = trim($namespace, '\\');

        $output = new StringOutput();

        Artisan::call('admin:ext-build', [
            'name'          => $package,
            '--namespace'   => $namespace ?: 'default',
            '--theme'       => $type == 2,
            '--alias'       => $alias,
            '--description' => $description,
            '--author'      => $author,
            '--email'       => $email,
        ], $output);

        return sprintf('<pre class="bg-transparent text-white">%s</pre>', (string)$output->getContent());
    }

    public function store()
    {
        return $this->form()->store();
    }

}
