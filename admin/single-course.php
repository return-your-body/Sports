<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
  <!-- Site Title-->
  <title>Single course</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" type="text/css"
    href="https://fonts.googleapis.com/css2?family=Exo:wght@300;400;500;600;700&amp;display=swap">
  <link rel="stylesheet" href="css/bootstrap.css">
  <link rel="stylesheet" href="css/fonts.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .ie-panel {
      display: none;
      background: #212121;
      padding: 10px 0;
      box-shadow: 3px 3px 5px 0 rgba(0, 0, 0, .3);
      clear: both;
      text-align: center;
      position: relative;
      z-index: 1;
    }

    html.ie-10 .ie-panel,
    html.lt-ie-10 .ie-panel {
      display: block;
    }
  </style>
</head>

<body>
  <div class="ie-panel"><a href="http://windows.microsoft.com/en-US/internet-explorer/"><img
        src="images/ie8-panel/warning_bar_0000_us.jpg" height="42" width="820"
        alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today."></a>
  </div>
  <!-- Page preloader-->
  <div class="page-loader">
    <div class="preloader-inner">
      <svg class="preloader-svg" width="200" height="200" viewbox="0 0 100 100">
        <polyline class="line-cornered stroke-still" points="0,0 100,0 100,100" stroke-width="10" fill="none">
        </polyline>
        <polyline class="line-cornered stroke-still" points="0,0 0,100 100,100" stroke-width="10" fill="none">
        </polyline>
        <polyline class="line-cornered stroke-animation" points="0,0 100,0 100,100" stroke-width="10" fill="none">
        </polyline>
        <polyline class="line-cornered stroke-animation" points="0,0 0,100 100,100" stroke-width="10" fill="none">
        </polyline>
      </svg>
    </div>
  </div>
  <!-- Page-->
  <div class="page">
    <header class="section page-header">
      <!-- RD Navbar-->
      <div class="rd-navbar-wrap rd-navbar-centered">
        <nav class="rd-navbar" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed"
          data-md-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-fixed" data-xl-layout="rd-navbar-static"
          data-xxl-layout="rd-navbar-static" data-xxxl-layout="rd-navbar-static" data-lg-device-layout="rd-navbar-fixed"
          data-xl-device-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static"
          data-xxxl-device-layout="rd-navbar-static" data-stick-up-offset="1px" data-sm-stick-up-offset="1px"
          data-md-stick-up-offset="1px" data-lg-stick-up-offset="1px" data-xl-stick-up-offset="1px"
          data-xxl-stick-up-offset="1px" data-xxx-lstick-up-offset="1px" data-stick-up="true">
          <div class="rd-navbar-inner">
            <!-- RD Navbar Panel-->
            <div class="rd-navbar-panel">
              <!-- RD Navbar Toggle-->
              <button class="rd-navbar-toggle" data-rd-navbar-toggle=".rd-navbar-nav-wrap"><span></span></button>
              <!-- RD Navbar Brand-->
              <div class="rd-navbar-brand">
                <!--Brand--><a class="brand-name" href="index.php"><img class="logo-default"
                    src="images/logo-default-172x36.png" alt="" width="86" height="18" loading="lazy" /><img
                    class="logo-inverse" src="images/logo-inverse-172x36.png" alt="" width="86" height="18"
                    loading="lazy" /></a>
              </div>
            </div>
            <div class="rd-navbar-nav-wrap">
              <ul class="rd-navbar-nav">
                <li class="rd-nav-item"><a class="rd-nav-link" href="a_index.php">網頁編輯</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="a_therapist.php">治療師時間表</a>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="teachers.php">新增診療項目</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-teacher.php">Single
                        teacher</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item active"><a class="rd-nav-link" href="courses.php">病患資料</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="single-course.php">黑名單</a>
                    </li>
                  </ul>
                </li>
                <li class="rd-nav-item"><a class="rd-nav-link" href="#">Pages</a>
                  <ul class="rd-menu rd-navbar-dropdown">
                    <li class="rd-dropdown-item"><a class="rd-dropdown-link" href="faqs.php">登出</a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>
            <div class="rd-navbar-collapse-toggle" data-rd-navbar-toggle=".rd-navbar-collapse"><span></span></div>
            <div class="rd-navbar-aside-right rd-navbar-collapse">
              <div class="rd-navbar-social">
                <div class="rd-navbar-social-text">Follow us</div>
                <ul class="list-inline">
                  <li><a class="icon novi-icon icon-default icon-custom-facebook" href="https://www.facebook.com/ReTurnYourBody/"></a></li>
                  <li><a class="icon novi-icon icon-default icon-custom-instagram" href="https://www.instagram.com/return_your_body/?igsh=cXo3ZnNudWMxaW9l"></a></li>
                </ul>
              </div>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <!-- Page Header-->
    <div class="section page-header breadcrumbs-custom-wrap bg-image bg-image-9">
      <!-- Breadcrumbs-->
      <section class="breadcrumbs-custom breadcrumbs-custom-svg">
        <div class="container">
          <p class="breadcrumbs-custom-subtitle">Single Course Page</p>
          <p class="heading-1 breadcrumbs-custom-title">Python programming</p>
          <ul class="breadcrumbs-custom-path">
            <li><a href="index.php">Home</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li class="active">Single course</li>
          </ul>
        </div>
      </section>

    </div>

    <!-- about service-->
    <section class="section section-lg text-center text-md-start bg-default">
      <div class="container">
        <div class="row row-ten row-50 justify-content-md-center justify-content-xl-between">
          <div class="col-md-8 col-lg-4">
            <h3>About the course</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et
              dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation.</p>
            <!-- Quote minimal-->
            <article class="quote-minimal">
              <p class="quote-minimal-text">This course will introduce fundamental programming concepts.</p>
            </article>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
              Excepteur sint occaecat cupidatat non proident.</p><a class="button button-primary button-nina"
              href="#">Order now</a>
          </div>
          <div class="col-md-8 col-lg-6 col-xl-5 text-center">
            <div class="image-position-01">
              <div class="blick-wrap"><img class="image-wrap" src="images/ipad-01-940x852.png" alt="" width="940"
                  height="852" />
                <div class="blick-overlay" data-blick-overlay="ipad"></div>
                <div class="blick-content"><img src="images/block-content-01-523x290.jpg" alt="" width="523"
                    height="290" loading="lazy" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-lg text-center bg-image bg-image-7 novi-bg novi-bg-img"
      data-preset='{"title":"Services 3","category":"services","reload":false,"id":"services-3"}'>
      <div class="container">
        <div class="row justify-content-sm-center row-50">
          <div class="col-sm-6 col-lg-3">
            <div class="thumbnail-classic unit flex-column">
              <div class="unit-left">
                <div class="thumbnail-classic-icon"><span class="icon novi-icon mdi mdi-thumb-up-outline"></span></div>
              </div>
              <div class="thumbnail-classic-caption unit-body">
                <h6 class="thumbnail-classic-title">Individual approach</h6>
                <p class="thumbnail-classic-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                  eiusmod.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="thumbnail-classic unit flex-column">
              <div class="unit-left">
                <div class="thumbnail-classic-icon"><span class="icon novi-icon mdi mdi-account-multiple"></span></div>
              </div>
              <div class="thumbnail-classic-caption unit-body">
                <h6 class="thumbnail-classic-title">Qualified instructors</h6>
                <p class="thumbnail-classic-text">Ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                  nostrud.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="thumbnail-classic unit flex-column">
              <div class="unit-left">
                <div class="thumbnail-classic-icon"><span class="icon novi-icon mdi mdi-headset"></span></div>
              </div>
              <div class="thumbnail-classic-caption unit-body">
                <h6 class="thumbnail-classic-title">24/7 online support</h6>
                <p class="thumbnail-classic-text">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum
                  dolore.</p>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-lg-3">
            <div class="thumbnail-classic unit flex-column">
              <div class="unit-left">
                <div class="thumbnail-classic-icon"><span class="icon novi-icon mdi mdi-credit-card"></span></div>
              </div>
              <div class="thumbnail-classic-caption unit-body">
                <h6 class="thumbnail-classic-title">Payment Methods</h6>
                <p class="thumbnail-classic-text">Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
                  officia.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Success stories from our students-->
    <section class="section section-lg novi-bg novi-bg-img bg-gray-100">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-xl-10">
            <h3>Success stories from our students</h3>
            <p class="big">Feel free to learn more about our courses from our students and the trustworthy testimonials
              they have recently submitted.</p>
          </div>
        </div>
        <div class="owl-carousel owl-layout-6" data-items="1" data-lg-items="2" data-loop="true" data-margin="40"
          data-autoplay="true" data-owl='{"nav":true}'>
          <div class="quote-boxed-2">
            <div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-14-86x86.jpg" alt=""
                width="86" height="86" loading="lazy" />
            </div>
            <div class="quote-boxed-2-body">
              <h6 class="quote-boxed-2-title">Easy-to-understand courses</h6>
              <div class="quote-boxed-2-text">The material that instructors of Pract use is pretty straightforward yet
                quite detailed. It’s also very useful to both newbies and professionals. You can choose any course to
                get started.</div>
              <div class="quote-boxed-2-cite small">Leslie Alexander</div>
            </div>
          </div>
          <div class="quote-boxed-2">
            <div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-15-86x86.jpg" alt=""
                width="86" height="86" loading="lazy" />
            </div>
            <div class="quote-boxed-2-body">
              <h6 class="quote-boxed-2-title">A wide selection of courses</h6>
              <div class="quote-boxed-2-text">I prefer the range of courses offerd by this online school to any other
                website. The team of Pract regularly updates their courses list and provides them at a very affordable
                price.</div>
              <div class="quote-boxed-2-cite small">Jane Cooper</div>
            </div>
          </div>
          <div class="quote-boxed-2">
            <div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-16-86x86.jpg" alt=""
                width="86" height="86" loading="lazy" />
            </div>
            <div class="quote-boxed-2-body">
              <h6 class="quote-boxed-2-title">A great knowledge source</h6>
              <div class="quote-boxed-2-text">As a web developer, I’m always trying to learn more about new development
                tips and tricks. Your online courses provided me with this unique possibility. Thank you!</div>
              <div class="quote-boxed-2-cite small">James wilson</div>
            </div>
          </div>
          <div class="quote-boxed-2">
            <div class="quote-boxed-2-media"><img class="quote-boxed-2-img" src="images/home-19-86x86.jpg" alt=""
                width="86" height="86" loading="lazy" />
            </div>
            <div class="quote-boxed-2-body">
              <h6 class="quote-boxed-2-title">#1 knowledge solution</h6>
              <div class="quote-boxed-2-text">The online courses allow greater flexibility in planning your day and
                working around family. With Pract, you can also be sure of improving your professional level.</div>
              <div class="quote-boxed-2-cite small">Peter Adams</div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section class="section section-lg bg-default text-center">
      <div class="container">
        <h3>Pricing</h3>
        <div class="row row-lg row-50 justify-content-sm-center">
          <!-- Pricing Box XL-->
          <div class="col-md-6 col-xl-4">
            <div class="pricing-box pricing-box-xl pricing-box-novi">
              <div class="pricing-box-header">
                <h4>Basic</h4>
              </div>
              <div class="pricing-box-price">
                <div class="heading-2"><sup>$</sup>49.00</div>
              </div><a class="button button-sm button-primary button-nina" href="#">order now</a>
              <div class="pricing-box-body">
                <ul class="pricing-box-list">
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-account-multiple"></span></div>
                      <div class="unit-body"><span>1 Course</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-harddisk"></span></div>
                      <div class="unit-body"><span>3 Weekly Assignments</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-repeat"></span></div>
                      <div class="unit-body"><span>10 Video Lectures</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-database"></span></div>
                      <div class="unit-body"><span>Weekly Feedback</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md icon-primary mdi mdi-email-open"></span></div>
                      <div class="unit-body"><span>E-mail Support</span></div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <!-- Pricing Box XL-->
          <div class="col-md-6 col-xl-4">
            <div class="pricing-box pricing-box-xl pricing-box-novi">
              <div class="pricing-box-header">
                <h4>Standard</h4>
              </div>
              <div class="pricing-box-price">
                <div class="heading-2"><sup>$</sup>69.00</div>
              </div><a class="button button-sm button-primary button-nina" href="#">order now</a>
              <div class="pricing-box-body">
                <ul class="pricing-box-list">
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-account-multiple"></span></div>
                      <div class="unit-body"><span>3 Courses</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-harddisk"></span></div>
                      <div class="unit-body"><span>5 Weekly Assignments</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-repeat"></span></div>
                      <div class="unit-body"><span>15 Video Lectures</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-database"></span></div>
                      <div class="unit-body"><span>Daily Feedback</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md icon-primary mdi mdi-email-open"></span></div>
                      <div class="unit-body"><span>Chat Support</span></div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <!-- Pricing Box XL-->
          <div class="col-md-6 col-xl-4">
            <div class="pricing-box pricing-box-xl pricing-box-novi">
              <div class="pricing-box-header">
                <h4>Gold</h4>
              </div>
              <div class="pricing-box-price">
                <div class="heading-2"><sup>$</sup>88.00</div>
              </div><a class="button button-sm button-primary button-nina" href="#">order now</a>
              <div class="pricing-box-body">
                <ul class="pricing-box-list">
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-account-multiple"></span></div>
                      <div class="unit-body"><span>5 Courses</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-harddisk"></span></div>
                      <div class="unit-body"><span>10 Weekly Assignments</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-repeat"></span></div>
                      <div class="unit-body"><span>20 Video Lectures</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md-big icon-primary mdi mdi-database"></span></div>
                      <div class="unit-body"><span>Instant Feedback</span></div>
                    </div>
                  </li>
                  <li>
                    <div class="unit unit-spacing-sm flex-row align-items-center">
                      <div class="unit-left"><span
                          class="icon novi-icon icon-md icon-primary mdi mdi-email-open"></span></div>
                      <div class="unit-body"><span>24/7 Support</span></div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="section section-lg bg-gray-100">
      <div class="container">
        <h3 class="text-center text-lg-start">Frequently Asked <br>Questions</h3>
        <div class="row row-ten row-50 justify-content-md-center justify-content-xl-between">
          <div class="col-md-9 col-lg-5 col-xxl-5">
            <!-- Bootstrap collapse-->
            <div class="accordion-custom-group accordion-custom-group-custom accordion-custom-group-corporate"
              id="accordion1" role="tablist" aria-multiselectable="false">
              <div class="accordion-custom-item accordion-custom-corporate">
                <h4 class="accordion-custom-heading" id="accordion1-accordion-head-ypbtktje">
                  <button class="accordion-custom-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accordion1-accordion-body-tbqgafhb"
                    aria-controls="accordion1-accordion-body-tbqgafhb" aria-expanded="true">How can I change something
                    in my order?<span class="accordion-custom-arrow"></span>
                  </button>
                </h4>
                <div class="accordion-custom-collapse collapse show" id="accordion1-accordion-body-tbqgafhb"
                  aria-labelledby="accordion1-accordion-head-ypbtktje" data-bs-parent="#accordion1">
                  <div class="accordion-custom-body">
                    <p>If you need to change something in your order, please contact us immediately. We usually process
                      orders within 30 minutes, and once we have processed your order, we will be unable to make any
                      changes.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-custom-item accordion-custom-corporate">
                <h4 class="accordion-custom-heading" id="accordion1-accordion-head-mruooyqr">
                  <button class="accordion-custom-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accordion1-accordion-body-mrrhqrwl"
                    aria-controls="accordion1-accordion-body-mrrhqrwl" aria-expanded="false">How can I pay for my
                    order?<span class="accordion-custom-arrow"></span>
                  </button>
                </h4>
                <div class="accordion-custom-collapse collapse" id="accordion1-accordion-body-mrrhqrwl"
                  aria-labelledby="accordion1-accordion-head-mruooyqr" data-bs-parent="#accordion1">
                  <div class="accordion-custom-body">
                    <p>We accept Visa, MasterCard, and American Express credit and debit cards for your convenience.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-custom-item accordion-custom-corporate">
                <h4 class="accordion-custom-heading" id="accordion1-accordion-head-ougepdos">
                  <button class="accordion-custom-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accordion1-accordion-body-dxmgtaua"
                    aria-controls="accordion1-accordion-body-dxmgtaua" aria-expanded="false">How long will my order take
                    to be delivered?<span class="accordion-custom-arrow"></span>
                  </button>
                </h4>
                <div class="accordion-custom-collapse collapse" id="accordion1-accordion-body-dxmgtaua"
                  aria-labelledby="accordion1-accordion-head-ougepdos" data-bs-parent="#accordion1">
                  <div class="accordion-custom-body">
                    <p>Delivery times will depend on your location. Once payment is confirmed your order will be
                      packaged. Delivery can be expected within a day.</p>
                  </div>
                </div>
              </div>
              <div class="accordion-custom-item accordion-custom-corporate">
                <h4 class="accordion-custom-heading" id="accordion1-accordion-head-gblxkqmc">
                  <button class="accordion-custom-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#accordion1-accordion-body-kskbdebi"
                    aria-controls="accordion1-accordion-body-kskbdebi" aria-expanded="false">Can I track my order?<span
                      class="accordion-custom-arrow"></span>
                  </button>
                </h4>
                <div class="accordion-custom-collapse collapse" id="accordion1-accordion-body-kskbdebi"
                  aria-labelledby="accordion1-accordion-head-gblxkqmc" data-bs-parent="#accordion1">
                  <div class="accordion-custom-body">
                    <p>Yes, you can! After placing your order you will receive an order confirmation via email. Each
                      order starts production 24 hours after your order is placed. Within 72 hours of you placing your
                      order, you will receive an expected delivery date.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-9 col-lg-5 col-xxl-4 d-none d-lg-block">
            <figure class="image-default"><img src="images/accordions-01-448x565.jpg" alt="" width="448" height="565" />
            </figure>
          </div>
        </div>
      </div>
    </section>

    <!-- do you need a consultation?-->
    <section class="section section-md bg-accent text-center text-md-start">
      <div class="container">
        <div class="row">
          <div class="col-xl-11">
            <div class="box-cta box-cta-inline">
              <div class="box-cta-inner">
                <h3 class="box-cta-title"><span class="box-cta-icon mdi mdi-case-sensitive-alt"></span><span>Free
                    cources</span></h3>
                <p>Free courses contain industry-relevant content and practical tasks and projects.</p>
              </div>
              <div class="box-cta-inner"><a class="button button-dark button-nina" href="a_therapist.php">Contact us</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Page Footer-->
    <footer class="section novi-bg novi-bg-img footer-simple">
      <div class="container">
        <div class="row row-40">
          <div class="col-md-4">
            <h4>About us</h4>
            <p class="me-xl-5">Pract is a learning platform for education and skills training. We provide you
              professional knowledge using innovative approach.</p>
          </div>
          <div class="col-md-3">
            <h4>Quick links</h4>
            <ul class="list-marked">
              <li><a href="index.php">Home</a></li>
              <li><a href="courses.php">Courses</a></li>
              <li><a href="a_therapist.php">About us</a></li>
              <li><a href="#">Blog</a></li>
              <li><a href="contacts.php">Contacts</a></li>
              <li><a href="#">Become a teacher</a></li>
            </ul>
          </div>
          <div class="col-md-5">
            <h4>Newsletter</h4>
            <p>Subscribe to our newsletter today to get weekly news, tips, and special offers from our team on the
              courses we offer.</p>
            <form class="rd-mailform rd-form-boxed" data-form-output="form-output-global" data-form-type="subscribe"
              method="post" action="bat/rd-mailform.php">
              <div class="form-wrap">
                <input class="form-input" type="email" name="email" data-constraints="@Email @Required"
                  id="footer-mail">
                <label class="form-label" for="footer-mail">Enter your e-mail</label>
              </div>
              <button class="form-button linearicons-paper-plane"></button>
            </form>
          </div>
        </div>
        <p class="rights"><span>&copy;&nbsp;</span><span
            class="copyright-year"></span><span>&nbsp;</span><span>Pract</span><span>.&nbsp;All Rights
            Reserved.&nbsp;</span><a href="privacy-policy.php">Privacy Policy</a> <a target="_blank"
            href="https://www.mobanwang.com/" title="网站模板">网站模板</a></p>
      </div>
    </footer>
  </div>
  <!-- Global Mailform Output-->
  <div class="snackbars" id="form-output-global"></div>
  <!-- Javascript-->
  <script src="js/core.min.js"></script>
  <script src="js/script.js"></script>
</body>

</html>