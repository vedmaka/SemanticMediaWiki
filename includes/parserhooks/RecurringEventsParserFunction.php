<?php

namespace SMW;

use Parser;

/**
 * Class that provides the {{#set_recurring_event}} parser function
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @see http://semantic-mediawiki.org/wiki/Help:Recurring_events
 *
 * @file
 *
 * @license GNU GPL v2+
 * @since   1.9
 *
 * @author mwjames
 */

/**
 * Class that provides the {{#set_recurring_event}} parser function
 *
 * @ingroup ParserFunction
 */
class RecurringEventsParserFunction extends SubobjectParserFunction {

	/** @var MessageFormatter */
	protected $settings;

	/** @var RecurringEvents */
	protected $events;

	/**
	 * @since 1.9
	 *
	 * @param IParserData $parserData
	 * @param Subobject $subobject
	 * @param MessageFormatter $msgFormatter
	 * @param Settings $settings
	 */
	public function __construct(
		IParserData $parserData,
		Subobject $subobject,
		MessageFormatter $msgFormatter,
		Settings $settings
	) {
		parent::__construct ( $parserData, $subobject, $msgFormatter );
		$this->settings = $settings;
	}

	/**
	 * Parse parameters, and update the ParserOutput with data from the
	 * RecurringEvents object
	 *
	 * @since 1.9
	 *
	 * @param ArrayFormatter $parameters
	 *
	 * @return string|null
	 */
	public function parse( ArrayFormatter $parameters ) {
		$this->setObjectReference( true );

		// Get recurring events
		$this->events = new RecurringEvents( $parameters->toArray(), $this->settings );
		$this->msgFormatter->addFromArray( $this->events->getErrors() );

		foreach ( $this->events->getDates() as $date_str ) {

			// Override existing parameters array with the returned
			// pre-processed parameters array from recurring events
			$parameters->setParameters( $this->events->getParameters() );

			// Add the date string as individual property / value parameter
			$parameters->addParameter( $this->events->getProperty(), $date_str );

			// Register object values
			// @see SubobjectParserFunction::addSubobjectValues
			$this->addSubobjectValues( $parameters );

			//  Each new $parameters set will add an additional subobject
			//  to the instance
			$this->parserData->getData()->addPropertyObjectValue(
				$this->subobject->getProperty(),
				$this->subobject->getContainer()
			);

			// Collect errors that occurred during processing
			$this->msgFormatter->addFromArray( $this->subobject->getErrors() );
		}

		// Update ParserOutput
		$this->parserData->updateOutput();

		return $this->msgFormatter->getHtml();
	}

	/**
	 * Parser::setFunctionHook {{#set_recurring_event}} handler method
	 *
	 * @param Parser $parser
	 *
	 * @return string|null
	 */
	public static function render( Parser &$parser ) {
		$instance = new self(
			new ParserData( $parser->getTitle(), $parser->getOutput() ),
			new Subobject( $parser->getTitle() ),
			new MessageFormatter( $parser->getTargetLanguage() ),
			Settings::newFromGlobals()
		);

		return $instance->parse( new ParserParameterFormatter( func_get_args() ) );
	}
}
