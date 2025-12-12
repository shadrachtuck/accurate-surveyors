<?php
namespace Elementor;

class Widget_Post_Type_Items extends Widget_Base {

	public function get_name() {
		return 'post_type_items';
	}

	public function get_title() {
		return __( 'Post Type Items', 'blockchain' );
	}

	public function get_icon() {
		return 'eicon-wordpress';
	}

	public function get_categories() {
		return [ 'blockchain-elements' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Post Type Items', 'blockchain' ),
			]
		);

		$this->add_control(
			'html_msg',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Displays a hand-picked selection of posts from a selected post type.', 'blockchain' ),
				'content_classes' => 'ci-description',
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'   => __( 'Post Type', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => blockchain_get_available_post_types(),
			]
		);

		$this->add_control(
			'selected_items',
			[
				'label'    => __( 'Select Items', 'blockchain' ),
				'type'     => Controls_Manager::SELECT2,
				'options'  => '',
				'multiple' => true,
			]
		);

		$this->add_control(
			'columns',
			[
				'label'   => __( 'Columns', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'2' => __( 'Two', 'blockchain' ),
					'3' => __( 'Three', 'blockchain' ),
					'4' => __( 'Four', 'blockchain' ),
				],
			]
		);

		$this->add_control(
			'view',
			[
				'label'   => __( 'View', 'blockchain' ),
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Post Type Items Element Styles', 'blockchain' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Text Color', 'blockchain' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}};',
				],
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'   => 'typography',
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings();

		if ( empty( $settings['selected_items'] ) ) {
			return;
		}

		$columns         = $settings['columns'];
		$post_type_items = $settings['selected_items'];
		$post_type       = $settings['post_type'];

		$q = new \WP_Query( array(
			'post_type'      => $post_type,
			'posts_per_page' => - 1,
			'post__in'       => $post_type_items,
			'orderby'        => 'post__in',
		) );

		if ( $q->have_posts() ) {
			?><div class="row row-items"><?php

				while ( $q->have_posts() ) {
					$q->the_post();

					?><div class="<?php echo esc_attr( blockchain_get_columns_classes( $columns ) ); ?>"><?php

					get_template_part( 'template-parts/widgets/home-item', $post_type );

					?></div><?php
				}
				wp_reset_postdata();

			?></div><?php
		}

	}

}
