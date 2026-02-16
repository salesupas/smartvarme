import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const { defaultSquareMeters, defaultInsulation } = attributes;

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Standardinnstillinger', 'smartvarme')}>
                    <RangeControl
                        label={__('Standard boligareal (m²)', 'smartvarme')}
                        value={defaultSquareMeters}
                        onChange={(value) => setAttributes({ defaultSquareMeters: value })}
                        min={20}
                        max={500}
                        step={10}
                    />
                    <SelectControl
                        label={__('Standard isolasjon', 'smartvarme')}
                        value={defaultInsulation}
                        options={[
                            { label: __('Dårlig', 'smartvarme'), value: 'poor' },
                            { label: __('Middels', 'smartvarme'), value: 'medium' },
                            { label: __('God', 'smartvarme'), value: 'good' },
                        ]}
                        onChange={(value) => setAttributes({ defaultInsulation: value })}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...useBlockProps()}>
                <div className="calculator-preview">
                    <h3>{__('Energikalkulator', 'smartvarme')}</h3>
                    <p>
                        {__('Standard: ', 'smartvarme')}
                        {defaultSquareMeters} m², {defaultInsulation} {__('isolasjon', 'smartvarme')}
                    </p>
                    <p className="notice">
                        {__('Kalkulatoren vil vises på frontend. Rediger standardverdier i sidepanelet →', 'smartvarme')}
                    </p>
                </div>
            </div>
        </>
    );
}
