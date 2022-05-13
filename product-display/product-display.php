<?php

/**
* Plugin Name: Product Display
* Version: 0.2
* Author: TapaCode
* Author URI: http://www.tapacode.com
*/

function product_display_shortcode($atts = [], $content = null) {

    global $product;

    // normalize attribute keys, lowercase
    $atts = array_change_key_case( (array) $atts, CASE_LOWER );

    // override default attributes with user attributes
    $tapa_atts = shortcode_atts(
        array(
        'id' => 1785,
        ), $atts, $tag
    );

    // start box
    $o = '<div class="product">';

    $product_id = esc_html__($tapa_atts['id'], 'tapa');
    $product = wc_get_product( $product_id);

    if ( ! is_null( $product) ) {
        $name = '';
        if ($atts['title']) {
            $name = esc_html__($atts['title'], 'tapa');
        } else {
            $name = $product->get_name();
        }
        $price = $product->get_price();
        $rating = $product->get_average_rating();
        $permalink = get_permalink( $product->get_id() );

        //extract decimal amnd whole numbers from ratings for stars
        $whole_rating = floor($rating);
        $fraction = $rating - $whole_rating;
        $fraction_string = str_replace('0.', '', round($fraction,1));
        $left_over = 5 - $whole_rating;

        if ($fraction) { $left_over--;}

        $o .= '<div class="elementor-element elementor-element-1a77b5e8 elementor-widget elementor-widget-heading"
            data-id="1a77b5e8" data-element_type="widget" data-widget_type="heading.default">
            <div class="elementor-widget-container">
            <style>
                .tapa-style {
                    color: var(--e-global-color-primary);
                    font-family: "Montserrat", Sans-serif;
                    font-size: 2.2rem;
                    font-weight: 600;
                    text-align: center;
                }
            </style>
                <h4 class="elementor-heading-title elementor-size-default tapa-style">' . $name . '</h4>
            </div>
        </div>';

        //for each whole number print a whole star
        //then print the remainder
        $stars = '';
        for ( $i = $rating; $i >= 1; $i--) {
            $stars .= '<i class="elementor-star-full"></i>';
        }
        $whole_rating = floor($rating);
        if ($fraction > 0) {
            $left_over - 1;
            $stars .= '<i class="elementor-star-'. $fraction_string .'"></i>';
        }
        if ($left_over) {
            for ($i = $left_over; $i >= 1; $i-- ){
               $stars .= '<i class="elementor-star-empty"></i>';
            }
        }

        $o .= '<div
            class="elementor-element elementor-element-d8d9726 elementor-star-rating--align-center elementor--star-style-star_fontawesome elementor-widget elementor-widget-star-rating"
            data-id="d8d9726" data-element_type="widget" data-widget_type="star-rating.default">
            <div class="elementor-widget-container">
                <style>
                    /*! elementor - v3.6.4 - 13-04-2022 */
                    .elementor-star-rating {
                        color: #ccd6df;
                        font-family: eicons;
                        display: inline-block
                    }

                    .elementor-star-rating i {
                        display: inline-block;
                        position: relative;
                        font-style: normal;
                        cursor: default
                    }

                    .elementor-star-rating i:before {
                        content: "\e934";
                        display: block;
                        font-size: inherit;
                        font-family: inherit;
                        position: absolute;
                        overflow: hidden;
                        color: #f0ad4e;
                        top: 0;
                        left: 0
                    }

                    .elementor-star-rating .elementor-star-empty:before {
                        content: none
                    }

                    .elementor-star-rating .elementor-star-1:before {
                        width: 10%
                    }

                    .elementor-star-rating .elementor-star-2:before {
                        width: 20%
                    }

                    .elementor-star-rating .elementor-star-3:before {
                        width: 30%
                    }

                    .elementor-star-rating .elementor-star-4:before {
                        width: 40%
                    }

                    .elementor-star-rating .elementor-star-5:before {
                        width: 50%
                    }

                    .elementor-star-rating .elementor-star-6:before {
                        width: 60%
                    }

                    .elementor-star-rating .elementor-star-7:before {
                        width: 70%
                    }

                    .elementor-star-rating .elementor-star-8:before {
                        width: 80%
                    }

                    .elementor-star-rating .elementor-star-9:before {
                        width: 90%
                    }

                    .elementor-star-rating__wrapper {
                        display: -webkit-box;
                        display: -ms-flexbox;
                        display: flex;
                        -webkit-box-align: center;
                        -ms-flex-align: center;
                        align-items: center
                    }

                    .elementor-star-rating__title {
                        margin-right: 10px
                    }

                    .elementor-star-rating--align-right .elementor-star-rating__wrapper {
                        text-align: right;
                        -webkit-box-pack: end;
                        -ms-flex-pack: end;
                        justify-content: flex-end
                    }

                    .elementor-star-rating--align-left .elementor-star-rating__wrapper {
                        text-align: left;
                        -webkit-box-pack: start;
                        -ms-flex-pack: start;
                        justify-content: flex-start
                    }

                    .elementor-star-rating--align-center .elementor-star-rating__wrapper {
                        text-align: center;
                        -webkit-box-pack: center;
                        -ms-flex-pack: center;
                        justify-content: center
                    }

                    .elementor-star-rating--align-justify .elementor-star-rating__title {
                        margin-right: auto
                    }

                    @media (max-width:1024px) {
                        .elementor-star-rating-tablet--align-right .elementor-star-rating__wrapper {
                            text-align: right;
                            -webkit-box-pack: end;
                            -ms-flex-pack: end;
                            justify-content: flex-end
                        }

                        .elementor-star-rating-tablet--align-left .elementor-star-rating__wrapper {
                            text-align: left;
                            -webkit-box-pack: start;
                            -ms-flex-pack: start;
                            justify-content: flex-start
                        }

                        .elementor-star-rating-tablet--align-center .elementor-star-rating__wrapper {
                            text-align: center;
                            -webkit-box-pack: center;
                            -ms-flex-pack: center;
                            justify-content: center
                        }

                        .elementor-star-rating-tablet--align-justify .elementor-star-rating__title {
                            margin-right: auto
                        }
                    }

                    @media (max-width:767px) {
                        .elementor-star-rating-mobile--align-right .elementor-star-rating__wrapper {
                            text-align: right;
                            -webkit-box-pack: end;
                            -ms-flex-pack: end;
                            justify-content: flex-end
                        }

                        .elementor-star-rating-mobile--align-left .elementor-star-rating__wrapper {
                            text-align: left;
                            -webkit-box-pack: start;
                            -ms-flex-pack: start;
                            justify-content: flex-start
                        }

                        .elementor-star-rating-mobile--align-center .elementor-star-rating__wrapper {
                            text-align: center;
                            -webkit-box-pack: center;
                            -ms-flex-pack: center;
                            justify-content: center
                        }

                        .elementor-star-rating-mobile--align-justify .elementor-star-rating__title {
                            margin-right: auto
                        }
                    }

                    .last-star {
                        letter-spacing: 0
                    }

                    .elementor--star-style-star_unicode .elementor-star-rating {
                        font-family: Arial, Helvetica, sans-serif
                    }

                    .elementor--star-style-star_unicode .elementor-star-rating i:not(.elementor-star-empty):before {
                        content: "\002605"
                    }
                </style>
                <div class="elementor-star-rating__wrapper">
                    <div class="elementor-star-rating" title="'. round($rating,1) .'/5" itemtype="http://schema.org/Rating"
                        itemscope=""
                        itemprop="reviewRating">
                        ' . $stars . '
                        <span itemprop="ratingValue" class="elementor-screen-only">'. round($rating,1) .'/5</span></div>
                </div>
            </div>
        </div>';

        if ( ! is_null( $content ) ) {
             $o .= '<div class="elementor-element elementor-element-22ea3e3c elementor-widget elementor-widget-text-editor"
                    data-id="22ea3e3c" data-element_type="widget" data-widget_type="text-editor.default">
                        <div class="elementor-widget-container">
                            <div class="elementor-text-editor elementor-clearfix" style="font-weight:400;text-align:center;">
                                ' . $content . '
                            </div>
                        </div>
                    </div>';
        }


        $o .= '<span style="text-align:center;font-weight:600;font-size:1.4rem;display:block;margin-bottom:20px;">' . wc_price( $price, $args ) . '</span>';

        $o .= '<div class="elementor-element elementor-element-fa0879d elementor-align-center elementor-widget elementor-widget-button"
            data-id="fa0879d" data-element_type="widget" data-widget_type="button.default">
            <div class="elementor-widget-container">
                <div class="elementor-button-wrapper">
                    <a href="' . $permalink . '"
                        class="elementor-button-link elementor-button elementor-size-sm" role="button">
                        <span class="elementor-button-content-wrapper">
                            <span class="elementor-button-text">More Info</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>';
    }

    // end box
    $o .= '</div>';

    //return output
    return $o;
}

function add_product_display_shortcode() {
    add_shortcode( 'tapa', 'product_display_shortcode' );
}

add_action( 'init', 'add_product_display_shortcode' );

?>