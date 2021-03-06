<script type="text/javascript" src="style/select_iteration.js"></script>

<?php
	use GraphAware\Neo4j\Client\ClientBuilder;

	$databaseURL 	= $_SESSION['DATABASE_URL'];
	$databasePort 	= $_SESSION['DATABASE_PORT'];
	$username		= $_SESSION['USERNAME'];
	$password 		= $_SESSION['PASSWORD'];
	
	require_once "vendor/autoload.php";

	require_once "functions/database_functions.php";



	$fullURL = "bolt://".$username.":".$password."@".$databaseURL.":".$databasePort;
	$client = ClientBuilder::create()
		->addConnection('bolt', $fullURL)
		->build();




	//Get projects list
	$query = "MATCH (p:Project) RETURN p.name as project";
	$result = runQuery($client, $query);

	$projectOptions = "<option value='none' selected>...</option>";

	foreach ($result->records() as $record) {
		$project = $record->value("project");
		$projectOptions.= "<option value='$project'";
		$projectOptions.= ">$project</option>";
	}
?>

<div class="row">
	<h1 class="center">Features to test</h1>
</div>

<div class="row">
	<form class="col-lg-8 col-lg-offset-2 form-horizontal" 
			method="post" action="index.php?features">
		
		<div class="row form-group" id="project_select">
			<label class="col-lg-3">Select your project </label>
			<select class="col-lg-3" name="project">
				<?php
					echo $projectOptions;
				?>
			</select>
		</div>

		<div class="row form-group" id="iteration_select">
			<label class="col-lg-3">Select the iteration</label>
			<select class="col-lg-3" name="iteration">
				<?php
					//echo $iterationOptions;
				?>
			</select>
		</div>

		<div class="row form-group" id="iteration_submit">
			<button class="btn btn-primary" type="submit" 
					name="iterationSubmit">Validate
			</button>
		</div>

	</form>
</div>

				
				

	<?php

	if (isset($_POST['iterationSubmit'])) {
		$project = $_POST['project'];
		$iteration = $_POST['iteration'];


		if ($iteration == "none" || $project == "none") {
			echo "<br>invalid input.<br>";
			exit();
		}

		$outputList = "<ul>";

		$query = "MATCH (p:Project {name: '$project'}) 
					MATCH (i:Iteration {name: '$iteration'})-[:IS_ITERATION_OF]->(p)
					MATCH (i)<-[:BELONGS_TO]-(files)-[:IS_INCLUDED_IN|:IS_REQUIRED_IN|:IS_USED_BY|:IMPACTS|:DECLARES*0..]->(feature:Feature {project: '$project'})
					WITH feature
					ORDER BY feature.name ASC 
					RETURN DISTINCT feature.name AS feature";

		$result = runQuery($client, $query);

		foreach ($result->records() as $record) {
			$outputList.= "<li>".$record->value('feature')."</li>";
		}
		$outputList.= "</ul>";

		echo "<h2>$project -> $iteration</h2><br>";
		echo $outputList;
	}
?>
