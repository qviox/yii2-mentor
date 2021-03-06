<?php
namespace qviox\mentor\controllers\api;

use qviox\mentor\controllers\api\base\ConfigController;
use qviox\mentor\models\scores\CompetitionQuestionnaire;
use qviox\mentor\models\scores\Skill;
use qviox\mentor\models\scores\SkillUserPoint;
use qviox\mentor\models\scores\Task;
use qviox\mentor\models\scores\TaskInputValue;
use qviox\mentor\models\scores\Team;
use qviox\mentor\models\search\TaskQuestionnaireSearch;
use yii\web\Controller;
use qviox\mentor\models\scores\UserTask;
use qviox\mentor\models\scores\TaskInput;
use yii\db\Exception;
use Yii;
class AjaxReactController extends  ConfigController {

    public function actionGetUsersRate()
    {

        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Не авторизированный пользователь');
        }
        return [
            [
                'name' => 'Рейтинг участников',
                'rangData' => UserTask::getTotalRate()
            ],
            [
                'name' => 'Задания участников',
                'taskData' => UserTask::getAllUserTasks()
            ]
        ];

    }
    public function actionGetTotalPointsBySession()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Не авторизированный пользователь');
        }

        return [
            'name' => 'Общий балл',
            'sessions' => SkillUserPoint::getTotalPointBySession()
        ];
    }
    public function actionGetUserSkills()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Не авторизированный пользовтель');
        }

        return Skill::getSkillsUserData();
    }
    public function actionGetTeamsRate()
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Не авторизированный пользователь');
        }

        return Team::getTotalRate();
    }
    public function actionSetCompetitionQuestionnaire()
    {

        if (Yii::$app->request->isPost) {

            $q = new CompetitionQuestionnaire();

            $q->load(Yii::$app->request->post());
            if (!Yii::$app->user->isGuest) $q->user_id = Yii::$app->user->identity->id;
            if ($q->validate()) {

                $q->save();
                return true;
            } else return $q->errors;


        }

    }
    public function actionCheckTaskQuestionnaire($taskId)
    {
        if (Yii::$app->user->isGuest) {
            throw new ForbiddenHttpException('Не авторизированный пользователь');
        }
        return TaskInputValue::find()
            ->joinWith('taskInput as ti')
            ->where(['user_id' => Yii::$app->user->id])
            ->andWhere(['ti.task_id' => $taskId])
            ->exists();
    }

    public function actionSetTaskQuestionnaire(){
            $post=Yii::$app->request->post();
            if($post){
                $task_id=$post['task_id'];
                $task=Task::findOne($task_id);
                unset($post['task_id']);
                return $task->saveTaskQuestionnaire($post);
            }
        return false;
    }


    public function actionCheckLessonProjectWorkSheet()
    {

        return Yii::$app->user->identity->lessonWorkSheet ? 0 : 1;

    }
    public function actionGetLessonProjectWorkSheets($params)
    {
        return LessonProjectWorksheets::find()->where(['show' => 1])->asArray()->all();
    }
    public function actionGetTaskQuestionnare()
    {
        $post=Yii::$app->request->post();
        if(!$post)
            return false;
        $task=Task::findOne($post['taskId']);
        if(!$task)
            return false;
        $searchModel = new  TaskQuestionnaireSearch();
        $searchModel->task_id=$task->id;
        $searchModel->filters[TaskQuestionnaireSearch::FilterByAccessLevel]=[TaskInput::ACCESS_LEVEL_PUBLIC];

        if($post['filterParams'])
            $searchModel->filters[TaskQuestionnaireSearch::FilterByVal]=$post['filterParams'];

        $searchModel->makeQuery();
var_dump(count($searchModel->query->all()));
die();
        return $searchModel->query->all();

    }

}