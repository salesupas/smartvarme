import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Button } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
    const { productIds } = attributes;
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        // Fetch WooCommerce products
        apiFetch({ path: '/wc/v3/products?per_page=50' })
            .then((data) => {
                setProducts(data);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    }, []);

    const addProduct = () => {
        setAttributes({ productIds: [...productIds, 0] });
    };

    const removeProduct = (index) => {
        const newIds = [...productIds];
        newIds.splice(index, 1);
        setAttributes({ productIds: newIds });
    };

    const updateProduct = (index, productId) => {
        const newIds = [...productIds];
        newIds[index] = parseInt(productId);
        setAttributes({ productIds: newIds });
    };

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Produkter å sammenligne', 'smartvarme')}>
                    {productIds.map((id, index) => (
                        <div key={index} style={{ marginBottom: '16px' }}>
                            <SelectControl
                                label={__('Produkt ' + (index + 1), 'smartvarme')}
                                value={id}
                                options={[
                                    { label: __('Velg produkt...', 'smartvarme'), value: 0 },
                                    ...products.map((p) => ({
                                        label: p.name,
                                        value: p.id,
                                    })),
                                ]}
                                onChange={(value) => updateProduct(index, value)}
                            />
                            <Button isDestructive onClick={() => removeProduct(index)}>
                                {__('Fjern', 'smartvarme')}
                            </Button>
                        </div>
                    ))}
                    <Button isPrimary onClick={addProduct}>
                        {__('Legg til produkt', 'smartvarme')}
                    </Button>
                </PanelBody>
            </InspectorControls>

            <div {...useBlockProps()}>
                {loading ? (
                    <p>{__('Laster produkter...', 'smartvarme')}</p>
                ) : productIds.length === 0 ? (
                    <p>{__('Velg produkter i sidepanelet →', 'smartvarme')}</p>
                ) : (
                    <p>
                        {__('Sammenligner ' + productIds.filter((id) => id > 0).length + ' produkter', 'smartvarme')}
                    </p>
                )}
            </div>
        </>
    );
}
