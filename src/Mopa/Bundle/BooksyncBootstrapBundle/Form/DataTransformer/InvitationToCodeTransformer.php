<?php
namespace Mopa\Bundle\BooksyncBootstrapBundle\Form\DataTransformer;

use Doctrine\ORM\NoResultException;
use Mopa\Bundle\BooksyncBundle\Entity\Invitation;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms an Invitation to an invitation code.
 */
class InvitationToCodeTransformer implements DataTransformerInterface
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Invitation) {
            throw new UnexpectedTypeException($value, 'Mopa\Bundle\BooksyncBundle\Entity\Invitation');
        }

        return $value->getCode();
    }

    public function reverseTransform($value)
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        try{
            $invitation = $this->entityManager
                ->getRepository('Mopa\Bundle\BooksyncBundle\Entity\Invitation')
                ->createQueryBuilder("i")
                ->leftJoin('i.user', 'u')
                ->where("i.code = :value")
                ->setParameter('value', $value)
                ->andWhere("u IS NULL")
                ->getQuery()
                ->getSingleResult()
            ;
        }
        catch(NoResultException $e){
            return null;
        }
        return $invitation;
    }
}