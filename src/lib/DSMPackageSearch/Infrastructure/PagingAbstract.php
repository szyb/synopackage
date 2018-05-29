<?php

namespace DSMPackageSearch\Infrastructure;

abstract class PagingAbstract
{
  public $pagesArray;

  private $itemsPerPage;
  private $allItems;
  private $displayItems;
  private $totalPages;
  public $currentPage;
  
  public function __construct($itemsPerPage, $allItems)
  {
    if ($itemsPerPage <= 0)
      throw new \Exception('Cannot set zero or less for items per page');
    $this->itemsPerPage = $itemsPerPage;
    $this->allItems = $allItems;
    $this->totalPages = ceil(count($allItems) / $this->itemsPerPage);
    $this->SetPage(1);
  }
 

  public function SetPage($pageNumer)
  {
    $this->displayItems = array();
        
    if ($pageNumer > $this->totalPages)
        $pageNumer = $this->totalPages;
    if ($pageNumer <= 0)
        $pageNumer = 1;
    $this->currentPage = $pageNumer;
    $startAt = ($pageNumer - 1) * $this->itemsPerPage;
    $endAt = $pageNumer * $this->itemsPerPage;
    $idx = $startAt;
    $i = 0;
    while ($idx < $endAt && $idx < count($this->allItems))
    {
        $this->displayItems[$i] = $this->allItems[$idx];
        $i++;
        $idx++;
    }
    
    $this->pagesArray = array();
    for ($i = 1; $i <= $this->totalPages; $i++)
    {
        $pageDetail = new PageDetail();
        $pageDetail->pageNumber = $i;
        if ($i == $pageNumer)
            $pageDetail->isCurrentPage = true;
        else 
            $pageDetail->isCurrentPage = false;
        $this->pagesArray[$i-1] = $pageDetail;
    }
  }

  public function GetItems()
  {
    return $this->displayItems;
  }

  public function GetPagesDetails()
  {
    return $this->pagesArray;
  }

  public function GetTotalPages()
  {
    return $this->totalPages;
  }
}

?>