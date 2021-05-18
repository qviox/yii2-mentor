<?php

namespace qviox\mentor\models\scores;

use qviox\mentor\models\search\UserSearch;
use qviox\mentor\models\User;
use Yii;
use yii\data\Sort;

/**
 * This is the model class for table "{{%user_task}}".
 *
 * @property int $user_id
 * @property int $task_id
 * @property int|null $point
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Task $task
 * @property User $user
 */
class UserTask extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%mentor_user_task}}';
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getTotalRate()
    {
        $select[]='user.id';
        $select[]='user.email';
        $name=Yii::$app->getModule('mentor')->userAttributes['name'];
        $surname=Yii::$app->getModule('mentor')->userAttributes['surname'];
        $table = Yii::$app->db->schema->getTableSchema('user');
        if(isset($table->columns[$name]) && $name)
            $select[]= 'user.'.$name;
        if(isset($table->columns[$surname]) && $surname)
            $select[]='user.'.$surname;
        $select[]='SUM(mentor_user_task.point) as score';
        $users = User::find()
            ->joinWith('userTasks')
            ->select($select)
            ->where(['type' => User::TYPE_PARTICIPANT])
            ->groupBy('user.id')
            ->orderBy(['score' => SORT_DESC])
            ->asArray()
            ->all();

        $userId = Yii::$app->user->id;
        $key = array_search($userId, array_column($users, 'id'));
        if ($key !== false && array_key_exists($key, $users)) {
            $users[$key]['user'] = true;
        }

        return $users;
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllUserTasks()
    {
        return self::find()
            ->joinWith('task')
            ->select('mentor_user_task.point as score, mentor_task.name as name')
            ->where(['user_id' => Yii::$app->user->id])
            ->asArray()
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'task_id'], 'required'],
            [['user_id', 'task_id', 'point'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id', 'task_id'], 'unique', 'targetAttribute' => ['user_id', 'task_id']],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Task::class, 'targetAttribute' => ['task_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'Участник',
            'task_id' => 'Задание',
            'point' => 'Баллы',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->isNewRecord || $changedAttributes['point'] != $this->point) {
            $team = $this->user->team;
            if ($team) {
                $team->reCount();
            }
        }

        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    public function afterDelete()
    {
        $team = $this->user->team;
        if ($team) {
            $team->reCount();
        }

        parent::afterDelete();
    }


    /**
     * Gets query for [[Task]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Task::class, ['id' => 'task_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
