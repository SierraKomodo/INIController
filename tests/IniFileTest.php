<?php

namespace SierraKomodo\INILib\Tests;

use PHPUnit\Framework\TestCase;
use SierraKomodo\INILib\IniFile;
use SierraKomodo\INILib\IniFileException;

/**
 * @coversDefaultClass \SierraKomodo\INILib\IniFile
 */
class IniFileTest extends TestCase
{
    protected $iniFile;
    protected $fileNamePrebuilt  = __DIR__ . DIRECTORY_SEPARATOR . "test_prebuilt.ini";
    protected $fileNameFake      = __DIR__ . DIRECTORY_SEPARATOR . "test_fake.ini";
    protected $fileNameEmpty     = __DIR__ . DIRECTORY_SEPARATOR . "test_empty.ini";
    protected $filePrebuiltContents;
    protected $filePrebuiltArray = array(
        'Section1' => array(
            'Key1' => 'Value1',
            'Key2' => 'Value2',
            'Key3' => 'Value3',
        ),
        'Section2' => array(
            'KeyA' => '1',
            'KeyB' => '2',
            'KeyC' => '3',
        )
    );
    
    
    protected function SetUp()
    {
        $this->filePrebuiltContents = str_replace("\r\n", PHP_EOL, <<<INI
[Section1]
Key1=Value1
Key2=Value2
Key3=Value3

[Section2]
KeyA=1
KeyB=2
KeyC=3


INI
        );
    }
    
    
    protected function TearDown()
    {
        if (file_exists($this->fileNamePrebuilt)) {
            unlink($this->fileNamePrebuilt);
        }
        if (file_exists($this->fileNameEmpty)) {
            unlink($this->fileNameEmpty);
        }
        if (file_exists($this->fileNameFake)) {
            unlink($this->fileNameFake);
        }
    }
    
    
    public function testConstructFailsToOpenNonExistantFile()
    {
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_FILE_NOT_EXIST);
        
        $this->iniFile = new IniFile($this->fileNameFake);
    }
    
    
    public function testConstructInstantiatesObject()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        self::assertInstanceOf(IniFile::class, $this->iniFile);
    }
    
    
    public function testConstructReadsEmptyFile()
    {
        touch($this->fileNamePrebuilt);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        self::assertInstanceOf(IniFile::class, $this->iniFile);
        self::assertEquals(array(), $this->iniFile->fetchDataArray());
    }
    
    
    public function testDeleteEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray = $this->filePrebuiltArray;
        unset($testArray['Section2']['KeyB']);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->deleteEntry('Section2', 'KeyB');
        $this->iniFile->deleteEntry('Section3', 'NonExistentKey');
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testDeleteEntryBlockedByReadOnly()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt, true);
        
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_READ_ONLY_MODE);
        $this->iniFile->deleteEntry('Section2', 'KeyB');
    }
    
    
    public function testDeleteSection()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray = $this->filePrebuiltArray;
        unset($testArray['Section2']);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->deleteSection('Section2');
        $this->iniFile->deleteSection('Section3');
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testDeleteSectionBlockedByReadOnly()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt, true);
        
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_READ_ONLY_MODE);
        $this->iniFile->deleteSection('Section2');
    }
    
    
    public function testFetchEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray     = $this->filePrebuiltArray;
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        self::assertEquals($testArray['Section1']['Key2'], $this->iniFile->fetchEntry('Section1', 'Key2'));
        self::assertEquals($testArray['Section2']['KeyC'], $this->iniFile->fetchEntry('Section2', 'KeyC'));
    }
    
    
    public function testFetchEntryReturnsNullForEmptyKey()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        self::assertEquals(null, $this->iniFile->fetchEntry('Section3', 'Foo'));
    }
    
    
    public function testFetchSection()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray     = $this->filePrebuiltArray;
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        self::assertEquals($testArray['Section1'], $this->iniFile->fetchSection('Section1'));
        self::assertEquals($testArray['Section2'], $this->iniFile->fetchSection('Section2'));
    }
    
    
    public function testFetchSectionReturnsNullForEmptyKey()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        self::assertEquals(null, $this->iniFile->fetchSection('Section3'));
    }
    
    
    public function testParseIniData()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        self::assertEquals($this->filePrebuiltArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSaveDataToFile()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $expectedString = str_replace("\r\n", PHP_EOL, <<<INI
[Section1]
Key1=Value1
Key2=Value2
Key3=Value3

[Section2]
KeyA=1
KeyB=2
KeyC=3

[Section3]
Foo=Bar


INI
        );
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setEntry('Section3', 'Foo', 'Bar');
        $this->iniFile->saveDataToFile();
        
        $fileContent = str_replace("\r\n", PHP_EOL, file_get_contents($this->fileNamePrebuilt));
        self::assertEquals($expectedString, $fileContent);
    }
    
    
    public function testSaveDataToFileBlockedByReadonly()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt, true);
        
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_READ_ONLY_MODE);
        
        $this->iniFile->saveDataToFile();
    }
    
    
    public function testSetEntryAddsNewEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section3']['Key2'] = 'Apple';
        $testArray['Section3']['KeyA'] = 'Orange';
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setEntry('Section3', 'Key2', 'Apple');
        $this->iniFile->setEntry('Section3', 'KeyA', 'Orange');
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSetEntryChangesExistingEntry()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section1']['Key2'] = 'Apple';
        $testArray['Section2']['KeyA'] = 'Orange';
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setEntry('Section1', 'Key2', 'Apple');
        $this->iniFile->setEntry('Section2', 'KeyA', 'Orange');
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSetEntryRejectsSymbolsInKey()
    {
        touch($this->fileNamePrebuilt);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_INVALID_PARAMETER);
        
        $this->iniFile->setEntry('Section', '[Key', 'Value');
    }
    
    
    public function testSetEntryRejectsSymbolsInSection()
    {
        touch($this->fileNamePrebuilt);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_INVALID_PARAMETER);
        
        $this->iniFile->setEntry(' [Section', 'Key', 'Value');
    }
    
    
    public function testSetEntryRejectsLineBreaks()
    {
        touch($this->fileNamePrebuilt);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_INVALID_PARAMETER);
        
        $this->iniFile->setEntry('Section', 'Key', "Val\r\nue");
    }
    
    
    public function testSetEntryRejectsReservedWordsInKey()
    {
        touch($this->fileNamePrebuilt);
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_INVALID_PARAMETER);
        
        $this->iniFile->setEntry('Section', 'On', "Value");
    }
    
    
    public function testSetEntryStripsWhitespace()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray                     = $this->filePrebuiltArray;
        $testArray['Section3']['Key2'] = 'Apple';
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setEntry('  Section3 ', "\tKey2\r", "Apple\r\n");
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSetEntryBlockedByReadonly()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt, true);
        
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_READ_ONLY_MODE);
        
        $this->iniFile->setEntry('Section', 'Key', 'Value');
    }
    
    
    public function testSetEntryAcceptsInteger()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        $this->iniFile->setEntry('Section', 'Key', 1);
        self::assertEquals(1, $this->iniFile->fetchEntry('Section', 'Key'));
    }
    
    
    public function testSetEntryAcceptsFloat()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        $this->iniFile->setEntry('Section', 'Key', 1.65);
        self::assertEquals(1.65, $this->iniFile->fetchEntry('Section', 'Key'));
    }
    
    
    public function testSetEntryAcceptsBool()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        
        $this->iniFile->setEntry('Section', 'Key', true);
        self::assertEquals(true, $this->iniFile->fetchEntry('Section', 'Key'));
    }
    
    
    public function testSetSectionAddsNewSection()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray = $this->filePrebuiltArray;
        
        $keyValuePairs         = array(
            'Alpha'   => 1,
            'Bravo'   => '2',
            'Charlie' => '3',
        );
        $testArray['Section3'] = $keyValuePairs;
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setSection('Section3', $keyValuePairs);
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSetSectionChangesExistingSection()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray = $this->filePrebuiltArray;
        
        $keyValuePairs         = array(
            'Alpha'   => 1,
            'Bravo'   => '2',
            'Charlie' => '3',
        );
        $testArray['Section2'] = $keyValuePairs;
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setSection('Section2', $keyValuePairs);
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSetSectionMergesExistingEntryWithArrayMerge()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray                        = $this->filePrebuiltArray;
        $testArray['Section2']['Alpha']   = 1;
        $testArray['Section2']['Bravo']   = '2';
        $testArray['Section2']['Charlie'] = '3';
        
        $keyValuePairs = array(
            'Alpha'   => 1,
            'Bravo'   => '2',
            'Charlie' => '3',
        );
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setSection('Section2', $keyValuePairs, true);
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSetSectionStripsWhitespace()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        $testArray = $this->filePrebuiltArray;
        
        $keyValuePairs         = array(
            'Alpha   '    => 1,
            '  Bravo'     => '2   ',
            '    Charlie' => '  3',
        );
        $testArray['Section3'] = array(
            'Alpha'   => 1,
            'Bravo'   => '2',
            'Charlie' => '3',
        );
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt);
        $this->iniFile->setSection('  Section3 ', $keyValuePairs);
        
        self::assertEquals($testArray, $this->iniFile->fetchDataArray());
    }
    
    
    public function testSetSectionBlockedByReadonly()
    {
        file_put_contents($this->fileNamePrebuilt, $this->filePrebuiltContents);
        
        $this->iniFile = new IniFile($this->fileNamePrebuilt, true);
        
        self::expectException(IniFileException::class);
        self::expectExceptionCode(IniFileException::ERR_READ_ONLY_MODE);
        
        $this->iniFile->setSection('Section', array('key' => 'value'));
    }
}
