<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }


    public function MyFindId($id)
    {
    //createQueryBuilder('p') => SELECT p FROM APP\ENTITY\PRODUCT p

    $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->setParameter('id',$id);

    // on recupere la requete        
    $query = $queryBuilder->getQuery();

    // on recupere les resultats
    //$result = $query->getOneOrNullResult();
    //$result = $query->getResult(); => recupere un tableau d'objets
    $result = $query->getOneOrNullResult();

    return $result;

    }

public function FindPrice($minPrice,$maxPrice){

    $queryBuilder = $this->createQueryBuilder('p')
      /*      ->where('p.price >= :minPrice')
            ->setParameter('minPrice',$minPrice*100)
            ->andwhere('p.price <= :maxPrice')
            ->setParameter('maxPrice',$maxPrice*100)*/

            ->where('p.price >= :minPrice AND p.price <= :maxPrice')
            ->setParameters(['minPrice'=>$minPrice*100,'maxPrice'=>$maxPrice*100])

            ->orderBy('p.price','DESC');

           // on recupere la requete        
    $query = $queryBuilder->getQuery();

    // on recupere les resultats
    //$result = $query->getOneOrNullResult();
    //$result = $query->getResult(); => recupere un tableau d'objets
    $result = $query->getResult();

    return $result;  

}

public function FindSearch($search){


    $queryBuilder = $this->createQueryBuilder('p')
    ->join('p.category','cat');
   // ->addSelect('cat');

if (count($search->getCategories()) > 0) {
    $queryBuilder
    ->where('cat.id IN (:categories)' )
    ->setParameter('categories',$search->getCategories());
} 

if (!empty($search->getString()))
{
    $mots = explode(' ',$search->getString());

    
    foreach ($mots as $cle => $mot) {
        
$queryBuilder
    ->andWhere('p.name LIKE :name'.$cle.' OR p.description LIKE :name'.$cle)
    ->setParameter('name'.$cle,'%'.$mot.'%');

    }


}

$query = $queryBuilder->getQuery();

// on recupere les resultats
//$result = $query->getOneOrNullResult();
//$result = $query->getResult(); => recupere un tableau d'objets
$result = $query->getResult();

return $result;


}


//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
