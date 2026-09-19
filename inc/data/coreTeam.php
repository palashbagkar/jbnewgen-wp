<?php
return array(
	array(
		'name' => 'Basundhara Bose',
		'role' => 'Director',
		'bio' => 'A history post graduate with a sharp business acumen, our proprietor brings vision, adaptability and a keen sense for successful outcome to every venture.',
		'photo' => '/images/team/basundhara-bose.jpeg',
	),
	array(
		'name' => 'Joyjeet Bose',
		'role' => 'Founder and CEO',
		'bio' => 'Over 34 years of experience in developing business strategies, digital transformation and driving business growth across industries.',
		'photo' => '/images/team/joyjeet-bose.jpeg',
		'linkedin' => 'https://www.linkedin.com/in/joyjeet-bose',
	),
	array(
		'name' => 'Suresh Doraiswamy',
		'role' => 'Technical Advisor',
		'bio' => 'Over 50 years of experience in Electronics Design, Embedded Software Development, Product Design, Destructive Technology Research, and Technology Transfer, developed integrated programs that explore cutting-edge applications of robotics, sensors, drones, and AI/ML systems in diverse fields.',
		'photo' => '/images/team/suresh-doraiswamy.jpg',
	),
	// Added directly on the live Payload CMS -- never landed in ../jbnewgen's
	// git-tracked content.ts, so this fallback would silently drop him if the
	// team_member CPT were ever emptied. Photo/bio pulled from the live site
	// per CLAUDE.md RULE 3 (../jbnewgen/UPDATE/ has no About/Team content).
	array(
		'name' => 'Palash Bagkar',
		'role' => 'Software Developer',
		'bio' => 'A developer working across web interfaces, content systems and cloud infrastructure, responsible for designing and building jbnewgen.com and the platform that keeps it running.',
		'photo' => '',
		'linkedin' => 'https://www.linkedin.com/in/invxazion/',
	),
);
