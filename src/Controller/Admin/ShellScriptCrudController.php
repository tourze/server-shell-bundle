<?php

namespace ServerShellBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use ServerShellBundle\Entity\ShellScript;

class ShellScriptCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ShellScript::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Shell脚本')
            ->setEntityLabelInPlural('Shell脚本')
            ->setPageTitle('index', 'Shell脚本列表')
            ->setPageTitle('detail', fn(ShellScript $script) => sprintf('脚本详情: %s', $script->getName()))
            ->setPageTitle('edit', fn(ShellScript $script) => sprintf('编辑脚本: %s', $script->getName()))
            ->setPageTitle('new', '新建Shell脚本')
            ->setHelp('index', '管理可在远程服务器上执行的Shell脚本')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['name', 'content', 'workingDirectory', 'tags', 'description'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm();

        yield TextField::new('name', '脚本名称')
            ->setRequired(true);

        yield TextareaField::new('content', '脚本内容')
            ->setRequired(true)
            ->hideOnIndex()
            ->setFormTypeOption('attr', ['rows' => 15])
            ->setHelp('脚本内容应以 #!/bin/bash 或其他适当的 shebang 开头');

        yield TextField::new('workingDirectory', '执行目录')
            ->hideOnIndex()
            ->setHelp('脚本将在此目录下执行');

        yield BooleanField::new('useSudo', '使用sudo执行');

        yield BooleanField::new('enabled', '是否启用');

        yield NumberField::new('timeout', '超时时间(秒)')
            ->setHelp('脚本执行超时时间，单位：秒')
            ->hideOnIndex();

        yield ArrayField::new('tags', '标签')
            ->hideOnIndex();

        yield TextareaField::new('description', '描述')
            ->hideOnIndex();

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        yield DateTimeField::new('updateTime', '更新时间')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('yyyy-MM-dd HH:mm:ss');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '脚本名称'))
            ->add(TextFilter::new('content', '脚本内容'))
            ->add(BooleanFilter::new('useSudo', '使用sudo执行'))
            ->add(BooleanFilter::new('enabled', '是否启用'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $executeAction = Action::new('execute', '执行脚本')
            ->linkToRoute('server_ssh_command_script_execute_form', fn (ShellScript $script) => [
                'id' => $script->getId(),
            ])
            ->setCssClass('btn btn-success')
            ->setIcon('fa fa-play');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $executeAction)
            ->add(Crud::PAGE_DETAIL, $executeAction)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, 'execute', Action::EDIT, Action::DELETE]);
    }
}
