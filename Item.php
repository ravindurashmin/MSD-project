<?php
/**
 * User: Abarajithan
 * Date: 12/19/2017
 * Time: 5:42 AM
 */

require_once("Database.php");
require_once ("ItemException.php");
require_once ("User.php");

class Item
{
    const ALLOWED_CATEGORIES = array("clothes", "books", "shoes", "sportsequipment", "electronicappliances", "musicialequipment", "furniture");
    const MAX_NO_VIEW = 10;
    const IMAGE_DIRECTORY = "item_images/";
    const ALLOWED_STATUSES = ["advertised", "confirmed by donor", "sent by donor", "received by admin", "sent by admin", "received by requester"];

    protected $item_id;
    protected $title;
    protected $description;
    protected $donor_id;
    protected $photo;
    protected $photoPath;
    protected $requester_id = null;
    protected $category;
    protected $status;
    protected $date_submitted;

    public function __construct()
    {
        $numArgs = func_num_args();

        if($numArgs == 9)
        {
            $this->setAll(func_get_arg(0),
                func_get_arg(1),
                func_get_arg(2),
                func_get_arg(3),
                func_get_arg(4),
                func_get_arg(5),
                func_get_arg(6),
                func_get_arg(7),
                func_get_arg(8) );
        }
    }

    public function setAll($item_id,$title,$description,$photoPath,$donor_id,
                           $requester_id,$category,$status,$date_submitted)
    {
        $this->item_id = $item_id;
        $this->title = $title;
        $this->description = $description;
        $this->donor_id = $donor_id;
        $this->photoPath = $photoPath;
        $this->requester_id=$requester_id;
        $this->category = $category;
        $this->status = $status;
        $this->date_submitted = $date_submitted;
    }

    public function makeFromForm($title, $description, $donor_id, $category)
    {
        try
        {
            $this->validateItem($title, $description, $donor_id, $category);

            $this->item_id = self::makeItemID();
            $this->status = 'advertised';

            // Upload Image & Get the path
            $this->photoPath = $this->uploadItemImage();

            return $this->writeToDB();
        }
        catch (ItemException $e)
        {
            $e->echoDetails();
            echo "Cannot create item";
        }
        catch (DatabaseException $e)
        {
            echo "Error Writing to database";
            $e->echoDetails();
        }

    }

    /**
     * @param $title
     * @param $description
     * @param $donor_id
     * @param $category
     * @throws ItemException
     */
    public function validateItem($title, $description, $donor_id, $category)
    {
        // 1. CLEAN UP the Title
        $this->title = ucwords(preg_replace("/[^a-zA-Z]/", "", $title));
        $this->description = $description;

        if (strlen($this->title) > 20) {
            throw new ItemException(
                __METHOD__, "Title too long: Title should be less than 20 characters in length."
            );
        }

        if (strlen($this->description) > 255) {
            throw new ItemException(
                __METHOD__, "Description too long: Description should be less than 255 characters in length."
            );
        }

        // 2. CLEAN UP the category and check if it is valid. If not return false

        $this->category = strtolower(preg_replace("/[^a-zA-Z]/", "", $category));

        if (!in_array($category, self::ALLOWED_CATEGORIES)) {
            throw new ItemException(
                __METHOD__, "Invalid Category.", [$category, self::ALLOWED_CATEGORIES]
            );
        }
        // 4. Validate donorID
        $this->donor_id = trim(strtolower($donor_id));

        try {
            $db = new Database();
            $db->select("donor", 'COUNT(user_id)', null, "user_id = '" . $this->donor_id . "'");
            if ($db->results[0][0] != 1) {
                throw new ItemException(
                    __METHOD__, "Donor doesn't exist", [$donor_id]
                );
            }
        }
        catch (DatabaseException $e)
        {
            $e->echoDetails();
        }
    }

    /**
     * @return bool|mysqli_result
     * @throws DatabaseException
     */
    public function writeToDB()
    {
        $db = new Database();
        return $db->insertInto('item', [$this->item_id, $this->title, $this->description,
            $this->photoPath, $this->donor_id, $this->requester_id, $this->category, $this->status, 'now()']);
    }

    /**This function searches the database for AVAILABLE items with equal CATEGORTY and Title LIKE Keyword string. R
     * Returns array of 10 or less objects
     * @param $category
     * @param $keywordString
     * @param Database $db
     * @return array
     */
    public static function returnAvailItems($category, $keywordString, Database $db)
    {
        try
        {
            $db->select('item', '*', null,
                Database::makeQueryForViewItems($category, $keywordString),
                null, null, self::MAX_NO_VIEW);

            $itemsList = array();

            foreach ($db->results as $row) {
                array_push($itemsList, new Item(
                    $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8]
                ));
            }
            return $itemsList;
        }
        catch (DatabaseException $e)
        {
            $e->echoDetails();
        }
        catch (Exception $e)
        {
            echo "Exception in method ". __METHOD__;
        }
    }


    /**This function searches the database for AVAILABLE items with equal CATEGORTY and Title LIKE Keyword string. R
     * Returns array of 10 or less objects
     * @param $category
     * @param $keywordString
     * @param Database $db
     * @return array
     */
    public static function returnDonatedItems(Database $db, $user = null)
    {
        try {
            if ($user == null) {

                $db->select('item LEFT', 'item.*, request.user_id', 'request ON (request.user_id = item.requester_id) AND (request.item_id = item.item_id)', "item.status != 'advertised'");

                $itemsRequestList = array();

                foreach ($db->results as $row)
                {

                    array_push($itemsRequestList, [new Item(
                        $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8]
                    ), $row[9]]
                    );
                }

                return $itemsRequestList;
            }
            else
            {
                $user_id = $user->getUserId();
                $where = "donor_id = '$user_id'";

                $db->select('item', '*', null, $where);

                $itemsList = array();

                foreach ($db->results as $row) {
                    array_push($itemsList, new Item(
                        $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8]
                    ));
                }

                return $itemsList;
            }
        }
        catch (DatabaseException $e)
        {
            $e->echoDetails();
        }
        catch (Exception $e)
        {
            echo "Exception in method ". __METHOD__;
        }
    }

    /**
     * @return int
     */
    static function makeItemID()
    {
        try
        {
            $db = new Database();
            $db->select("item", 'MAX(item_id)');

            if ($db->numResults == 0 || $db->results[0][0] == null)
            {
                return 1;
            }
            else
            {
                return (int)$db->results[0][0] + 1;
            }
        }
        catch (DatabaseException $e)
        {
            $e->echoDetails();
        }
        catch (Exception $e)
        {
            echo "Exception in method ". __METHOD__;
        }
    }

    public function uploadItemImage()
    {
        try
        {
            return Database::uploadImage(self::IMAGE_DIRECTORY, $this->item_id);
        }
        catch (DatabaseException $e)
        {
            $e->echoDetails();
        }
        catch (Exception $e)
        {
            echo "Exception in method ". __METHOD__;
        }
    }

    public function changeStatusTo($status, $requester_id = null)
    {
        $a = self::ALLOWED_STATUSES;

        if(!in_array($status, $a)) return;
        $db = new Database();

        if(($status == $a[1] && $this->status = $a[0]))
        {
            $this->requester_id = $requester_id;
            $db->update('item', "item_id = '$this->item_id'",['requester_id'],["$requester_id"]);
        }
        if (
            ($status == $a[1] && $this->status = $a[0])
            ||  ($status == $a[2] && $this->status == $a[1])
            ||  ($status == $a[3] && $this->status == $a[2])
            ||  ($status == $a[4] && $this->status == $a[3])
            ||  ($status == $a[5] && $this->status == $a[4])
        )
        {
            $this->status = $status;
            $db->update('item', "item_id = '$this->item_id'",['status'],["$status"]);
            Database::messageBox("Item status updated","Go back", "index.php");
        }

    }

    public function removeItem()
    {
        if ($this->status != self::ALLOWED_STATUSES[0])
        {
            echo "This item has been confirmed. Confirmed items cannot be removed";
            return;
        }
        $db = new Database();
        $db->delete('item', "item_id = $this->item_id");
        $db->delete('request', "item_id = $this->item_id");
        Database::messageBox("Item removed","Go back", "index.php");
    }






    public function getItemId()
    {
        return $this->item_id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDonorId()
    {
        return $this->donor_id;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function getPhotoPath()
    {
        return $this->photoPath;
    }

    public function getRequesterId()
    {
        return $this->requester_id;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDateSubmitted()
    {
        return $this->date_submitted;
    }




}