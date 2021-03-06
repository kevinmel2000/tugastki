<?php

/**
 * This is the model class for table "user_level".
 *
 * The followings are the available columns in table 'user_level':
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $p_id
 *
 * The followings are the available model relations:
 * @property ModulePrivilage[] $modulePrivilages
 * @property User[] $users
 */
class UserLevel extends CActiveRecord
{
	public $onDeleteMessage;
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserLevel the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user_level';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, description, p_id', 'required'),
			array('id, name, description, p_id', 'safe', 'on'=>'search'),
            array('name', 'unique', 'message' => 'Choose another name! This level name ({value}) already taken'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'modulePrivilages' => array(self::HAS_MANY, 'ModulePrivilage', 'user_level_id'),
			'users' => array(self::HAS_MANY, 'User', 'user_level_id'),
			'parent' => array(self::BELONGS_TO, 'UserLevel', 'p_id'),
			'childs' => array(self::HAS_MANY, 'UserLevel', 'p_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'description' => 'Description',
			'p_id' => 'Parent',
		);
	}
    
    public function scopes()
    {
        return array(
            'getParents' => array(
                'condition' => 'p_id = 0',
                'order' => 'name'
            ),
        );
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('p_id',$this->p_id);
		
		$criteria->order = 'p_id, name asc';
		 
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=> false,
			'pagination' => array(
                    'pagesize' => 40,
					)
		));
	}
	
	public function validationBeforeDelete()
    {
        $usedInModule = array();
        $this->onDeleteMessage = 'Cannot delete "'. $this->name . '", data already used in (';

        $criteria = new CDbCriteria();
        $criteria->compare('p_id', $this->id);

        $models = UserLevel::model()->findAll($criteria);

        if (isset($this->modulePrivilages) && count($this->modulePrivilages) > 0)
        {
            $usedInModule[] = 'Module';
        }

        if (isset($this->users) && count($this->users) > 0)
        {
            $usedInModule[] = 'User';
        }
        
        if(count($models) > 0)
        {
            $usedInModule[] = 'User Level';
        }
        
        if(count($usedInModule) > 0)
        {
            sort($usedInModule);
            foreach ($usedInModule as $value)
            {
                $this->onDeleteMessage .= $value .', ';
            }
            
            $this->onDeleteMessage = substr($this->onDeleteMessage, 0, strlen($this->onDeleteMessage) - 2);
            $this->onDeleteMessage .= '). Please delete related data first before delete it!';
            
            
            return false;
        }
        else
        {
            return true;
        }
    }
}