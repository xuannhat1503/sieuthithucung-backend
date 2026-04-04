package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.CartItemDto;
import com.sieuthithucung.entity.CartItemEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.CartItemMapper;
import com.sieuthithucung.repository.CartItemRepository;
import com.sieuthithucung.service.CartItemService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class CartItemServiceImpl implements CartItemService {

    private final CartItemRepository repository;

    public CartItemServiceImpl(CartItemRepository repository) {
        this.repository = repository;
    }
    @Override
    public CartItemDto create(CartItemDto dto) {
        return createInternal(dto);
    }

    @Override
    public CartItemDto update(Long id, CartItemDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Cart item not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public CartItemDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<CartItemDto> getAll() {
        return getAllInternal();
    }

    private CartItemDto createInternal(CartItemDto dto) {
        CartItemEntity entity = CartItemMapper.mapToCartItemEntity(dto);
        CartItemEntity saved = repository.save(entity);
        return CartItemMapper.mapToCartItemDto(saved);
    }

    private CartItemDto updateInternal(Long id, CartItemDto dto) {
        CartItemEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Cart item not found with id: " + id));

        CartItemEntity source = CartItemMapper.mapToCartItemEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        CartItemEntity saved = repository.save(existing);
        return CartItemMapper.mapToCartItemDto(saved);
    }

    private CartItemDto findByIdInternal(Long id) {
        CartItemEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Cart item not found with id: " + id));
        return CartItemMapper.mapToCartItemDto(entity);
    }

    private List<CartItemDto> getAllInternal() {
        return repository.findAll().stream().map(CartItemMapper::mapToCartItemDto).toList();
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
}