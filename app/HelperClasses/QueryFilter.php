<?php


namespace App\HelperClasses;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{

	protected $request;
	/**
	 * @var Builder
	 */
	protected $builder;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}


	public function apply(Builder $builder)
	{
		$this->builder = $builder;

		$filters = $this->filters();


		foreach ($this->filters() as $filterName => $filterValue)
		{
			if ( is_string( $filterValue ) ) {
				$filterValue = trim($filterValue);
			}

			if(method_exists($this, $filterName) && $filterValue)
			{
				$this->$filterName($filterValue);
			}
		}

		return $this->builder;
	}


	public function filters()
	{
		return $this->request->all();
	}
}