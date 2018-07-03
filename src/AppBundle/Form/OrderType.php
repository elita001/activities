<?php
namespace AppBundle\Form;

use AppBundle\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $submitText = 'Добавить мероприятие';
        $order = $options['data'];
        if ($order && $order instanceof Order && $order->getId() > 0) {
            $submitText = 'Редактировать мероприятие';
        }
        $builder
            ->add('name', null, array('label' => 'Название'))
            ->add('content', TextareaType::class, array('label' => 'Описание'))
            ->add('phone', IntegerType::class, array('label' => 'Телефон'))
            ->add('dateStart', DateTimeType::class, array('label' => 'Начинается', 'data' => new \DateTime('now')))
            ->add('dateEnd', DateTimeType::class, array('label' => 'Заканчивается', 'data' => new \DateTime('now')))
            ->add('logo', FileType::class, array('label' => 'Изображение'))
            ->add('submit', SubmitType::class, array('label' => $submitText))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Order::class,
        ));
    }
}