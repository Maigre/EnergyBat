<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2010 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2010 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.3c, 2010-06-01
 */


/** PHPExcel root directory */
if (!defined('ROOT')) {
	define('ROOT', dirname(__FILE__) . '/');
	require(ROOT . 'PHPExcel/Autoloader.php');
	Autoloader::Register();
	Shared_ZipStreamWrapper::register();
	// check mbstring.func_overload
	if (ini_get('mbstring.func_overload') & 2) {
		throw new Exception('Multibyte function overloading in PHP must be disabled for string functions (2).');
	}
}


/**
 * PHPExcel
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2010 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel
{
	/**
	 * Document properties
	 *
	 * @var DocumentProperties
	 */
	private $_properties;

	/**
	 * Document security
	 *
	 * @var DocumentSecurity
	 */
	private $_security;

	/**
	 * Collection of Worksheet objects
	 *
	 * @var Worksheet[]
	 */
	private $_workSheetCollection = array();

	/**
	 * Active sheet index
	 *
	 * @var int
	 */
	private $_activeSheetIndex = 0;

	/**
	 * Named ranges
	 *
	 * @var NamedRange[]
	 */
	private $_namedRanges = array();

	/**
	 * CellXf supervisor
	 *
	 * @var Style
	 */
	private $_cellXfSupervisor;

	/**
	 * CellXf collection
	 *
	 * @var Style[]
	 */
	private $_cellXfCollection = array();

	/**
	 * CellStyleXf collection
	 *
	 * @var Style[]
	 */
	private $_cellStyleXfCollection = array();

	/**
	 * Create a new PHPExcel with one Worksheet
	 */
	public function __construct()
	{
		// Initialise worksheet collection and add one worksheet
		$this->_workSheetCollection = array();
		$this->_workSheetCollection[] = new Worksheet($this);
		$this->_activeSheetIndex = 0;

		// Create document properties
		$this->_properties = new DocumentProperties();

		// Create document security
		$this->_security = new DocumentSecurity();

		// Set named ranges
		$this->_namedRanges = array();

		// Create the cellXf supervisor
		$this->_cellXfSupervisor = new Style(true);
		$this->_cellXfSupervisor->bindParent($this);

		// Create the default style
		$this->addCellXf(new Style);
		$this->addCellStyleXf(new Style);
	}


	public function disconnectWorksheets() {
		foreach($this->_workSheetCollection as $k => &$worksheet) {
			$worksheet->disconnectCells();
			$this->_workSheetCollection[$k] = null;
		}
		unset($worksheet);
		$this->_workSheetCollection = array();
	}

	/**
	 * Get properties
	 *
	 * @return DocumentProperties
	 */
	public function getProperties()
	{
		return $this->_properties;
	}

	/**
	 * Set properties
	 *
	 * @param DocumentProperties	$pValue
	 */
	public function setProperties(DocumentProperties $pValue)
	{
		$this->_properties = $pValue;
	}

	/**
	 * Get security
	 *
	 * @return DocumentSecurity
	 */
	public function getSecurity()
	{
		return $this->_security;
	}

	/**
	 * Set security
	 *
	 * @param DocumentSecurity	$pValue
	 */
	public function setSecurity(DocumentSecurity $pValue)
	{
		$this->_security = $pValue;
	}

	/**
	 * Get active sheet
	 *
	 * @return Worksheet
	 */
	public function getActiveSheet()
	{
		return $this->_workSheetCollection[$this->_activeSheetIndex];
	}

    /**
     * Create sheet and add it to this workbook
     *
     * @return Worksheet
     */
    public function createSheet($iSheetIndex = null)
    {
        $newSheet = new Worksheet($this);
        $this->addSheet($newSheet, $iSheetIndex);
        return $newSheet;
    }

    /**
     * Add sheet
     *
     * @param Worksheet $pSheet
	 * @param int|null $iSheetIndex Index where sheet should go (0,1,..., or null for last)
     * @return Worksheet
     * @throws Exception
     */
    public function addSheet(Worksheet $pSheet = null, $iSheetIndex = null)
    {
        if(is_null($iSheetIndex))
        {
            $this->_workSheetCollection[] = $pSheet;
        }
        else
        {
            // Insert the sheet at the requested index
            array_splice(
                $this->_workSheetCollection,
                $iSheetIndex,
                0,
                array($pSheet)
                );

			// Adjust active sheet index if necessary
			if ($this->_activeSheetIndex >= $iSheetIndex) {
				++$this->_activeSheetIndex;
			}

        }
		return $pSheet;
    }

	/**
	 * Remove sheet by index
	 *
	 * @param int $pIndex Active sheet index
	 * @throws Exception
	 */
	public function removeSheetByIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_workSheetCollection) - 1) {
			throw new Exception("Sheet index is out of bounds.");
		} else {
			array_splice($this->_workSheetCollection, $pIndex, 1);
		}
	}

	/**
	 * Get sheet by index
	 *
	 * @param int $pIndex Sheet index
	 * @return Worksheet
	 * @throws Exception
	 */
	public function getSheet($pIndex = 0)
	{
		if ($pIndex > count($this->_workSheetCollection) - 1) {
			throw new Exception("Sheet index is out of bounds.");
		} else {
			return $this->_workSheetCollection[$pIndex];
		}
	}

	/**
	 * Get all sheets
	 *
	 * @return Worksheet[]
	 */
	public function getAllSheets()
	{
		return $this->_workSheetCollection;
	}

	/**
	 * Get sheet by name
	 *
	 * @param string $pName Sheet name
	 * @return Worksheet
	 * @throws Exception
	 */
	public function getSheetByName($pName = '')
	{
		$worksheetCount = count($this->_workSheetCollection);
		for ($i = 0; $i < $worksheetCount; ++$i) {
			if ($this->_workSheetCollection[$i]->getTitle() == $pName) {
				return $this->_workSheetCollection[$i];
			}
		}

		return null;
	}

	/**
	 * Get index for sheet
	 *
	 * @param Worksheet $pSheet
	 * @return Sheet index
	 * @throws Exception
	 */
	public function getIndex(Worksheet $pSheet)
	{
		foreach ($this->_workSheetCollection as $key => $value) {
			if ($value->getHashCode() == $pSheet->getHashCode()) {
				return $key;
			}
		}
	}

    /**
	 * Set index for sheet by sheet name.
	 *
	 * @param string $sheetName Sheet name to modify index for
	 * @param int $newIndex New index for the sheet
	 * @return New sheet index
	 * @throws Exception
	 */
    public function setIndexByName($sheetName, $newIndex)
    {
        $oldIndex = $this->getIndex($this->getSheetByName($sheetName));
        $pSheet = array_splice(
            $this->_workSheetCollection,
            $oldIndex,
            1
            );
        array_splice(
            $this->_workSheetCollection,
            $newIndex,
            0,
            $pSheet
            );
        return $newIndex;
    }

	/**
	 * Get sheet count
	 *
	 * @return int
	 */
	public function getSheetCount()
	{
		return count($this->_workSheetCollection);
	}

	/**
	 * Get active sheet index
	 *
	 * @return int Active sheet index
	 */
	public function getActiveSheetIndex()
	{
		return $this->_activeSheetIndex;
	}

	/**
	 * Set active sheet index
	 *
	 * @param int $pIndex Active sheet index
	 * @throws Exception
	 * @return Worksheet
	 */
	public function setActiveSheetIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_workSheetCollection) - 1) {
			throw new Exception("Active sheet index is out of bounds.");
		} else {
			$this->_activeSheetIndex = $pIndex;
		}
		return $this->getActiveSheet();
	}

	/**
	 * Set active sheet index by name
	 *
	 * @param string $pValue Sheet title
	 * @return Worksheet
	 * @throws Exception
	 */
	public function setActiveSheetIndexByName($pValue = '')
	{
		if (($worksheet = $this->getSheetByName($pValue)) instanceof Worksheet) {
			$this->setActiveSheetIndex($worksheet->getParent()->getIndex($worksheet));
			return $worksheet;
		}

		throw new Exception('Workbook does not contain sheet:' . $pValue);
	}

	/**
	 * Get sheet names
	 *
	 * @return string[]
	 */
	public function getSheetNames()
	{
		$returnValue = array();
		$worksheetCount = $this->getSheetCount();
		for ($i = 0; $i < $worksheetCount; ++$i) {
			array_push($returnValue, $this->getSheet($i)->getTitle());
		}

		return $returnValue;
	}

	/**
	 * Add external sheet
	 *
	 * @param Worksheet $pSheet External sheet to add
	 * @param int|null $iSheetIndex Index where sheet should go (0,1,..., or null for last)
	 * @throws Exception
	 * @return Worksheet
	 */
	public function addExternalSheet(Worksheet $pSheet, $iSheetIndex = null) {
		if (!is_null($this->getSheetByName($pSheet->getTitle()))) {
			throw new Exception("Workbook already contains a worksheet named '{$pSheet->getTitle()}'. Rename the external sheet first.");
		}

		// count how many cellXfs there are in this workbook currently, we will need this below
		$countCellXfs = count($this->_cellXfCollection);

		// copy all the shared cellXfs from the external workbook and append them to the current
		foreach ($pSheet->getParent()->getCellXfCollection() as $cellXf) {
			$this->addCellXf(clone $cellXf);
		}

		// move sheet to this workbook
		$pSheet->rebindParent($this);

		// update the cellXfs
		foreach ($pSheet->getCellCollection(false) as $cellID) {
			$cell = $sheet->getCell($cellID);
			$cell->setXfIndex( $cell->getXfIndex() + $countCellXfs );
		}

		return $this->addSheet($pSheet, $iSheetIndex);
	}

	/**
	 * Get named ranges
	 *
	 * @return NamedRange[]
	 */
	public function getNamedRanges() {
		return $this->_namedRanges;
	}

	/**
	 * Add named range
	 *
	 * @param NamedRange $namedRange
	 * @return PHPExcel
	 */
	public function addNamedRange(NamedRange $namedRange) {
		if ($namedRange->getScope() == null) {
			// global scope
			$this->_namedRanges[$namedRange->getName()] = $namedRange;
		} else {
			// local scope
			$this->_namedRanges[$namedRange->getScope()->getTitle().'!'.$namedRange->getName()] = $namedRange;
		}
		return true;
	}

	/**
	 * Get named range
	 *
	 * @param string $namedRange
	 * @param Worksheet|null $pSheet Scope. Use null for global scope
	 * @return NamedRange|null
	 */
	public function getNamedRange($namedRange, Worksheet $pSheet = null) {
		$returnValue = null;

		if ($namedRange != '' && !is_null($namedRange)) {
			// first look for global defined name
			if (isset($this->_namedRanges[$namedRange])) {
				$returnValue = $this->_namedRanges[$namedRange];
			}

			// then look for local defined name (has priority over global defined name if both names exist)
			if (!is_null($pSheet) && isset($this->_namedRanges[$pSheet->getTitle() . '!' . $namedRange])) {
				$returnValue = $this->_namedRanges[$pSheet->getTitle() . '!' . $namedRange];
			}
		}

		return $returnValue;
	}

	/**
	 * Remove named range
	 *
	 * @param string $namedRange
	 * @param Worksheet|null $pSheet. Scope. Use null for global scope.
	 * @return PHPExcel
	 */
	public function removeNamedRange($namedRange, Worksheet $pSheet = null) {
		if (is_null($pSheet)) {
			if (isset($this->_namedRanges[$namedRange])) {
				unset($this->_namedRanges[$namedRange]);
			}
		} else {
			if (isset($this->_namedRanges[$pSheet->getTitle() . '!' . $namedRange])) {
				unset($this->_namedRanges[$pSheet->getTitle() . '!' . $namedRange]);
			}
		}
		return $this;
	}

	/**
	 * Get worksheet iterator
	 *
	 * @return WorksheetIterator
	 */
	public function getWorksheetIterator() {
		return new WorksheetIterator($this);
	}

	/**
	 * Copy workbook (!= clone!)
	 *
	 * @return PHPExcel
	 */
	public function copy() {
		$copied = clone $this;

		$worksheetCount = count($this->_workSheetCollection);
		for ($i = 0; $i < $worksheetCount; ++$i) {
			$this->_workSheetCollection[$i] = $this->_workSheetCollection[$i]->copy();
			$this->_workSheetCollection[$i]->rebindParent($this);
		}

		return $copied;
	}

	/**
	 * Implement PHP __clone to create a deep clone, not just a shallow copy.
	 */
	public function __clone() {
		foreach($this as $key => $val) {
			if (is_object($val) || (is_array($val))) {
				$this->{$key} = unserialize(serialize($val));
			}
		}
	}

	/**
	 * Get the workbook collection of cellXfs
	 *
	 * @return Style[]
	 */
	public function getCellXfCollection()
	{
		return $this->_cellXfCollection;
	}

	/**
	 * Get cellXf by index
	 *
	 * @param int $index
	 * @return Style
	 */
	public function getCellXfByIndex($pIndex = 0)
	{
		return $this->_cellXfCollection[$pIndex];
	}

	/**
	 * Get cellXf by hash code
	 *
	 * @param string $pValue
	 * @return Style|false
	 */
	public function getCellXfByHashCode($pValue = '')
	{
		foreach ($this->_cellXfCollection as $cellXf) {
			if ($cellXf->getHashCode() == $pValue) {
				return $cellXf;
			}
		}
		return false;
	}

	/**
	 * Get default style
	 *
	 * @return Style
	 * @throws Exception
	 */
	public function getDefaultStyle()
	{
		if (isset($this->_cellXfCollection[0])) {
			return $this->_cellXfCollection[0];
		}
		throw new Exception('No default style found for this workbook');
	}

	/**
	 * Add a cellXf to the workbook
	 *
	 * @param Style
	 */
	public function addCellXf(Style $style)
	{
		$this->_cellXfCollection[] = $style;
		$style->setIndex(count($this->_cellXfCollection) - 1);
	}

	/**
	 * Remove cellXf by index. It is ensured that all cells get their xf index updated.
	 *
	 * @param int $pIndex Index to cellXf
	 * @throws Exception
	 */
	public function removeCellXfByIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_cellXfCollection) - 1) {
			throw new Exception("CellXf index is out of bounds.");
		} else {
			// first remove the cellXf
			array_splice($this->_cellXfCollection, $pIndex, 1);

			// then update cellXf indexes for cells
			foreach ($this->_workSheetCollection as $worksheet) {
				foreach ($worksheet->getCellCollection(false) as $cellID) {
					$cell = $sheet->getCell($cellID);
					$xfIndex = $cell->getXfIndex();
					if ($xfIndex > $pIndex ) {
						// decrease xf index by 1
						$cell->setXfIndex($xfIndex - 1);
					} else if ($xfIndex == $pIndex) {
						// set to default xf index 0
						$cell->setXfIndex(0);
					}
				}
			}
		}
	}

	/**
	 * Get the cellXf supervisor
	 *
	 * @return Style
	 */
	public function getCellXfSupervisor()
	{
		return $this->_cellXfSupervisor;
	}

	/**
	 * Get the workbook collection of cellStyleXfs
	 *
	 * @return Style[]
	 */
	public function getCellStyleXfCollection()
	{
		return $this->_cellStyleXfCollection;
	}

	/**
	 * Get cellStyleXf by index
	 *
	 * @param int $pIndex
	 * @return Style
	 */
	public function getCellStyleXfByIndex($pIndex = 0)
	{
		return $this->_cellStyleXfCollection[$pIndex];
	}

	/**
	 * Get cellStyleXf by hash code
	 *
	 * @param string $pValue
	 * @return Style|false
	 */
	public function getCellStyleXfByHashCode($pValue = '')
	{
		foreach ($this->_cellXfStyleCollection as $cellStyleXf) {
			if ($cellStyleXf->getHashCode() == $pValue) {
				return $cellStyleXf;
			}
		}
		return false;
	}

	/**
	 * Add a cellStyleXf to the workbook
	 *
	 * @param Style $pStyle
	 */
	public function addCellStyleXf(Style $pStyle)
	{
		$this->_cellStyleXfCollection[] = $pStyle;
		$pStyle->setIndex(count($this->_cellStyleXfCollection) - 1);
	}

	/**
	 * Remove cellStyleXf by index
	 *
	 * @param int $pIndex
	 * @throws Exception
	 */
	public function removeCellStyleXfByIndex($pIndex = 0)
	{
		if ($pIndex > count($this->_cellStyleXfCollection) - 1) {
			throw new Exception("CellStyleXf index is out of bounds.");
		} else {
			array_splice($this->_cellStyleXfCollection, $pIndex, 1);
		}
	}

	/**
	 * Eliminate all unneeded cellXf and afterwards update the xfIndex for all cells
	 * and columns in the workbook
	 */
	public function garbageCollect()
	{
    	// how many references are there to each cellXf ?
		$countReferencesCellXf = array();
		foreach ($this->_cellXfCollection as $index => $cellXf) {
			$countReferencesCellXf[$index] = 0;
		}

		foreach ($this->getWorksheetIterator() as $sheet) {

			// from cells
			foreach ($sheet->getCellCollection(false) as $cellID) {
				$cell = $sheet->getCell($cellID);
				++$countReferencesCellXf[$cell->getXfIndex()];
			}

			// from row dimensions
			foreach ($sheet->getRowDimensions() as $rowDimension) {
				if ($rowDimension->getXfIndex() !== null) {
					++$countReferencesCellXf[$rowDimension->getXfIndex()];
				}
			}

			// from column dimensions
			foreach ($sheet->getColumnDimensions() as $columnDimension) {
				++$countReferencesCellXf[$columnDimension->getXfIndex()];
			}
		}

		// remove cellXfs without references and create mapping so we can update xfIndex
		// for all cells and columns
		$countNeededCellXfs = 0;
		foreach ($this->_cellXfCollection as $index => $cellXf) {
			if ($countReferencesCellXf[$index] > 0 || $index == 0) { // we must never remove the first cellXf
				++$countNeededCellXfs;
			} else {
				unset($this->_cellXfCollection[$index]);
			}
			$map[$index] = $countNeededCellXfs - 1;
		}
		$this->_cellXfCollection = array_values($this->_cellXfCollection);

		// update the index for all cellXfs
		foreach ($this->_cellXfCollection as $i => $cellXf) {
			$cellXf->setIndex($i);
		}

		// make sure there is always at least one cellXf (there should be)
		if (count($this->_cellXfCollection) == 0) {
			$this->_cellXfCollection[] = new Style();
		}

		// update the xfIndex for all cells, row dimensions, column dimensions
		foreach ($this->getWorksheetIterator() as $sheet) {

			// for all cells
			foreach ($sheet->getCellCollection(false) as $cellID) {
				$cell = $sheet->getCell($cellID);
				$cell->setXfIndex( $map[$cell->getXfIndex()] );
			}

			// for all row dimensions
			foreach ($sheet->getRowDimensions() as $rowDimension) {
				if ($rowDimension->getXfIndex() !== null) {
					$rowDimension->setXfIndex( $map[$rowDimension->getXfIndex()] );
				}
			}

			// for all column dimensions
			foreach ($sheet->getColumnDimensions() as $columnDimension) {
				$columnDimension->setXfIndex( $map[$columnDimension->getXfIndex()] );
			}
		}

		// also do garbage collection for all the sheets
		foreach ($this->getWorksheetIterator() as $sheet) {
			$sheet->garbageCollect();
		}
	}

}
