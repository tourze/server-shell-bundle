<?php

namespace ServerShellBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use ServerShellBundle\Entity\ScriptExecution;
use ServerShellBundle\Enum\CommandStatus;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ScriptExecutionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ScriptExecution::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('脚本执行记录')
            ->setEntityLabelInPlural('脚本执行记录')
            ->setPageTitle('index', '脚本执行记录列表')
            ->setPageTitle('detail', fn(ScriptExecution $execution) => sprintf('执行记录详情: %s', $execution->getScript()->getName()))
            ->setPageTitle('edit', fn(ScriptExecution $execution) => sprintf('编辑执行记录: %s', $execution->getScript()->getName()))
            ->setHelp('index', '查看脚本执行历史记录')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm();

        yield AssociationField::new('node', '服务器节点')
            ->setRequired(true)
            ->setFormTypeOption('choice_label', 'name');

        yield AssociationField::new('script', 'Shell脚本')
            ->setRequired(true)
            ->setFormTypeOption('choice_label', 'name');

        yield ChoiceField::new('status', '状态')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => CommandStatus::class])
            ->formatValue(function ($value) {
                if (!$value instanceof CommandStatus) {
                    return '未知';
                }

                return $value->getLabel();
            });

        yield TextareaField::new('result', '执行结果')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormTypeOption('disabled', true);

        yield DateTimeField::new('executedAt', '执行时间')
            ->hideOnForm()
            ->setFormat('yyyy-MM-dd HH:mm:ss');

        yield NumberField::new('executionTime', '执行耗时(秒)')
            ->hideOnForm()
            ->setNumDecimals(3);

        yield NumberField::new('exitCode', '退出码')
            ->hideOnForm();

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
            ->add(EntityFilter::new('node', '服务器节点'))
            ->add(EntityFilter::new('script', 'Shell脚本'))
            ->add(ChoiceFilter::new('status', '状态')
                ->setChoices([
                    '待执行' => CommandStatus::PENDING->value,
                    '执行中' => CommandStatus::RUNNING->value,
                    '已完成' => CommandStatus::COMPLETED->value,
                    '失败' => CommandStatus::FAILED->value,
                    '超时' => CommandStatus::TIMEOUT->value,
                    '已取消' => CommandStatus::CANCELED->value,
                ]))
            ->add(DateTimeFilter::new('executedAt', '执行时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'));
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT)
            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::DELETE]);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): \Doctrine\ORM\QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->select('entity')
            ->leftJoin('entity.node', 'node')
            ->leftJoin('entity.script', 'script')
            ->orderBy('entity.createTime', 'DESC');
    }
}
