<?php include(__DIR__ . '/common/header.php'); ?>
<main class="container my-5">
	<!-- Content Section -->
	<div class="mx-auto px-auto my-5 py-5 text-center">
		<h1 class="display-4 fw-bold">The Fantasy Fencing Project</h1>
		<div class="col-lg-10 mx-auto">
			<p class="fs-3 lead mb-3">Bringing international fencing to everyone!</p>
		</div>
	</div>
	<div class="container mx-auto px-auto my-5 py-5">
		<h2 class="pb-2 border-bottom">Features</h2>
		<div class="row g-5 py-5 row-cols-1 row-cols-lg-3">
			<div class="col d-flex align-items-start">
				<div>
					<h3 class="fs-2 text-body-emphasis">Search Athletes</h3>
					<p>Easily view a variety of information about your favorite international athletes!</p>
					<a href="/a/search.php" class="btn btn-primary">Search Athletes</a>
				</div>
			</div>
			<div class="col d-flex align-items-start">
				<div>
					<h3 class="fs-2 text-body-emphasis">View Competitions</h3>
					<p>Easily view the details and results of any international competition!</p>
					<a href="/c/search.php" class="btn btn-primary">Search Competitions</a>
				</div>
			</div>
			<div class="col d-flex align-items-start">
				<div>
					<h3 class="fs-2 text-body-emphasis">Play Fantasy</h3>
					<p>Participate in our fantasy game! Earn points based on your favorite athlete's performance!</p>
					<a href="/fantasy/competitions.php" class="btn btn-primary">Play Fantasy</a>
				</div>
			</div>
		</div>
	</div>
</main>
<?php include(__DIR__ . '/common/footer.php'); ?>