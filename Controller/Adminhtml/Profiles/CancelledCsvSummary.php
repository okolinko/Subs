<?php

namespace Toppik\Subscriptions\Controller\Adminhtml\Profiles;

use \Magento\Backend\App\Action;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Ui\Component\MassAction\Filter;
use \Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;

class CancelledCsvSummary extends Action
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var object
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param Action\Context $context
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        FileFactory $fileFactory,
        \Toppik\Subscriptions\Model\ResourceModel\Profile\Cancelled\CollectionFactory $collectionFactory,
        Filesystem $filesystem
    )
    {
        parent::__construct($context);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->filter = $filter;
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        // prepare file
        $component = $this->filter->getComponent();
        $name = md5(microtime());
        $file = 'export/' . $component->getName() . $name . '.csv';
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        // headers
        $stream->writeCsv(['#', 'Сancellation reason', 'Quantity', '%']);

        // prepare data
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $reasonsCounter = [];
        $totalCount = 0;

        foreach ($collection->getItems() as $item) {
            $totalCount++;
            $reasonName = $item->getMessage();
            if (!isset($reasonsCounter [$reasonName])) {
                $reasonsCounter [$reasonName] = 1;
            } else {
                $reasonsCounter [$reasonName]++;
            }
        }

        // handle and write data
        /** @var array $reason = [ '{list num} 1', '{Reason name} Too expensive', '{Quantity} 17', '{Percent} 11']; */
        $i = 1;
        arsort($reasonsCounter);
        $summ_Quantity = 0;
        $summ_Percent = 0;
        foreach ($reasonsCounter as $reasonName => $reasonCountHere) {
            $reason = [$i, $this->dropCap($reasonName), $reasonCountHere, number_format($reasonCountHere / $totalCount * 100, 2)];
            $stream->writeCsv($reason);
            $summ_Quantity += $reasonCountHere;
            $summ_Percent += $reasonCountHere / $totalCount * 100;
            $i++;
        }
        $stream->writeCsv(['', '', '', '']);
        $stream->writeCsv(['', '', $summ_Quantity, $summ_Percent]);
        $stream->unlock();
        $stream->close();

        return $this->fileFactory->create($component->getName() . '_summary.csv', ['type' => 'filename', 'value' => $file, 'rm' => true], 'var');
    }

    /**
     * Capitalize first character and uncapitalize others in sentence
     * @param $string
     * @return string
     */
    public function dropCap($string)
    {
        $sentences = explode('.', $string);
        $result = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (mb_strlen($sentence) > 0) {
                $cap = strtoupper(mb_substr($sentence, 0, 1));
                $not_cap = strtolower(mb_substr($sentence, 1));
                $result[] = ' ' . $cap . $not_cap;
            } else {
                $result[] = ' ';
            }
        }

        return trim(implode('.', $result));
    }
}