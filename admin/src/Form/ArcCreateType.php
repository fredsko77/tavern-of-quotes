<?php

namespace Admin\Form;

use App\Entity\Arc;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArcCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Nom de l\'arc',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Position',
                'required' => false,
            ])
            ->add('isLocked', CheckboxType::class, [
                'label' => 'Verouiller',
                'required' => false,
            ])
            ->add('uploadedCsv', FileType::class, [
                'label' => 'Importer (.csv)',
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,
                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,
                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypesMessage' => 'Please upload a valid CSV document',
                    ])
                ],
                'attr' => [
                    'accept' => '.csv'
                ]
            ])
            ->add('Enregistrer', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Arc::class,
        ]);
    }
}
